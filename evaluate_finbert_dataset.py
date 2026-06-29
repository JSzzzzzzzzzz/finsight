#!/usr/bin/env python3
"""
Evaluate the manually labelled FinSight CryptoCompare headline dataset by using
the existing local FinBERT Flask endpoint.

Default input:
    finbert_validation_dataset_one_reviewer.csv

Outputs:
    finbert_validation_results.csv
    finbert_metrics_summary.csv
    finbert_confusion_matrix.csv

Run the FinBERT service first:
    cd ai_service
    python app.py

Then, from the FinSight project root:
    python evaluate_finbert_dataset.py
"""

from __future__ import annotations

import argparse
import csv
import json
import sys
import urllib.error
import urllib.request
from pathlib import Path
from typing import Dict, List, Tuple

VALID_LABELS = ("positive", "negative", "neutral")


def normalise_label(value: str) -> str:
    return value.strip().lower()


def read_dataset(path: Path) -> Tuple[List[dict], List[str]]:
    if not path.exists():
        raise FileNotFoundError(f"Input file not found: {path}")

    with path.open("r", encoding="utf-8-sig", newline="") as handle:
        reader = csv.DictReader(handle)
        if reader.fieldnames is None:
            raise ValueError("The CSV file does not contain a header row.")

        required = {"headline", "researcher_label"}
        missing = required.difference(reader.fieldnames)
        if missing:
            raise ValueError(
                "Missing required column(s): " + ", ".join(sorted(missing))
            )

        rows = list(reader)
        fieldnames = list(reader.fieldnames)

    if not rows:
        raise ValueError("The dataset contains no records.")

    errors = []
    for index, row in enumerate(rows, start=2):
        headline = (row.get("headline") or "").strip()
        label = normalise_label(row.get("researcher_label") or "")

        if not headline:
            errors.append(f"Row {index}: headline is empty.")
        if label not in VALID_LABELS:
            errors.append(
                f"Row {index}: researcher_label must be positive, negative, "
                f"or neutral; received {label!r}."
            )

        row["headline"] = headline
        row["researcher_label"] = label

    if errors:
        preview = "\n".join(errors[:20])
        extra = "" if len(errors) <= 20 else f"\n...and {len(errors) - 20} more."
        raise ValueError(
            "Complete and correct all researcher labels before evaluation:\n"
            + preview + extra
        )

    return rows, fieldnames


def call_finbert(endpoint: str, headlines: List[str]) -> List[dict]:
    payload = json.dumps({"texts": headlines}).encode("utf-8")
    request = urllib.request.Request(
        endpoint,
        data=payload,
        headers={"Content-Type": "application/json"},
        method="POST",
    )

    try:
        with urllib.request.urlopen(request, timeout=180) as response:
            body = json.loads(response.read().decode("utf-8"))
    except urllib.error.URLError as exc:
        raise RuntimeError(
            "Unable to connect to the FinBERT service. Start ai_service/app.py "
            f"and confirm the endpoint is available at {endpoint}. Details: {exc}"
        ) from exc

    results = body.get("results")
    if not isinstance(results, list):
        raise RuntimeError(
            "Unexpected FinBERT response. The 'results' array was not returned."
        )
    if len(results) != len(headlines):
        raise RuntimeError(
            f"FinBERT returned {len(results)} results for {len(headlines)} headlines."
        )

    return results


def safe_divide(numerator: int, denominator: int) -> float:
    return numerator / denominator if denominator else 0.0


def calculate_metrics(rows: List[dict]) -> Tuple[List[dict], Dict[str, Dict[str, int]], float]:
    confusion = {
        actual: {predicted: 0 for predicted in VALID_LABELS}
        for actual in VALID_LABELS
    }

    correct_total = 0
    for row in rows:
        actual = row["researcher_label"]
        predicted = row["finbert_prediction"]
        confusion[actual][predicted] += 1
        if actual == predicted:
            correct_total += 1

    metrics = []
    for label in VALID_LABELS:
        tp = confusion[label][label]
        fp = sum(confusion[actual][label] for actual in VALID_LABELS if actual != label)
        fn = sum(confusion[label][predicted] for predicted in VALID_LABELS if predicted != label)
        support = sum(confusion[label].values())

        precision = safe_divide(tp, tp + fp)
        recall = safe_divide(tp, tp + fn)
        f1 = safe_divide(2 * precision * recall, precision + recall)

        metrics.append({
            "sentiment_class": label,
            "precision": precision,
            "recall": recall,
            "f1_score": f1,
            "number_of_headlines": support,
        })

    accuracy = safe_divide(correct_total, len(rows))
    macro_precision = sum(item["precision"] for item in metrics) / len(metrics)
    macro_recall = sum(item["recall"] for item in metrics) / len(metrics)
    macro_f1 = sum(item["f1_score"] for item in metrics) / len(metrics)

    metrics.append({
        "sentiment_class": "macro_average",
        "precision": macro_precision,
        "recall": macro_recall,
        "f1_score": macro_f1,
        "number_of_headlines": len(rows),
    })
    metrics.append({
        "sentiment_class": "overall_accuracy",
        "precision": "",
        "recall": "",
        "f1_score": accuracy,
        "number_of_headlines": len(rows),
    })

    return metrics, confusion, accuracy


def write_results(path: Path, rows: List[dict], original_fields: List[str]) -> None:
    required_output_fields = [
        "finbert_prediction",
        "positive_probability",
        "negative_probability",
        "neutral_probability",
        "correct",
    ]

    fieldnames = list(original_fields)
    for field in required_output_fields:
        if field not in fieldnames:
            fieldnames.append(field)

    with path.open("w", encoding="utf-8-sig", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(rows)


def write_metrics(path: Path, metrics: List[dict]) -> None:
    with path.open("w", encoding="utf-8-sig", newline="") as handle:
        writer = csv.DictWriter(
            handle,
            fieldnames=[
                "sentiment_class",
                "precision",
                "recall",
                "f1_score",
                "number_of_headlines",
            ],
        )
        writer.writeheader()

        for row in metrics:
            formatted = dict(row)
            for key in ("precision", "recall", "f1_score"):
                if isinstance(formatted[key], float):
                    formatted[key] = f"{formatted[key]:.4f}"
            writer.writerow(formatted)


def write_confusion(path: Path, confusion: Dict[str, Dict[str, int]]) -> None:
    with path.open("w", encoding="utf-8-sig", newline="") as handle:
        writer = csv.writer(handle)
        writer.writerow([
            "actual_sentiment",
            "predicted_positive",
            "predicted_negative",
            "predicted_neutral",
        ])

        for actual in VALID_LABELS:
            writer.writerow([
                actual,
                confusion[actual]["positive"],
                confusion[actual]["negative"],
                confusion[actual]["neutral"],
            ])


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument(
        "--input",
        default="finbert_validation_dataset_one_reviewer.csv",
        help="Manually labelled CSV dataset.",
    )
    parser.add_argument(
        "--endpoint",
        default="http://127.0.0.1:5001/analyze-sentiment",
        help="FinBERT Flask endpoint.",
    )
    parser.add_argument(
        "--results-output",
        default="finbert_validation_results.csv",
    )
    parser.add_argument(
        "--metrics-output",
        default="finbert_metrics_summary.csv",
    )
    parser.add_argument(
        "--confusion-output",
        default="finbert_confusion_matrix.csv",
    )
    args = parser.parse_args()

    try:
        rows, original_fields = read_dataset(Path(args.input))
        headlines = [row["headline"] for row in rows]

        print(f"Submitting {len(headlines)} headlines to FinBERT...")
        predictions = call_finbert(args.endpoint, headlines)

        for row, prediction in zip(rows, predictions):
            predicted_label = normalise_label(str(prediction.get("sentiment", "")))
            if predicted_label not in VALID_LABELS:
                raise RuntimeError(
                    f"Unexpected FinBERT sentiment label: {predicted_label!r}"
                )

            row["finbert_prediction"] = predicted_label
            row["positive_probability"] = prediction.get("positive", "")
            row["negative_probability"] = prediction.get("negative", "")
            row["neutral_probability"] = prediction.get("neutral", "")
            row["correct"] = 1 if row["researcher_label"] == predicted_label else 0

        metrics, confusion, accuracy = calculate_metrics(rows)

        write_results(Path(args.results_output), rows, original_fields)
        write_metrics(Path(args.metrics_output), metrics)
        write_confusion(Path(args.confusion_output), confusion)

        macro = next(item for item in metrics if item["sentiment_class"] == "macro_average")

        print("\nEvaluation completed.")
        print(f"Overall accuracy : {accuracy:.4f} ({accuracy * 100:.2f}%)")
        print(f"Macro precision  : {macro['precision']:.4f}")
        print(f"Macro recall     : {macro['recall']:.4f}")
        print(f"Macro F1-score   : {macro['f1_score']:.4f}")

        print("\nConfusion matrix (actual rows × predicted columns):")
        print("             positive  negative  neutral")
        for actual in VALID_LABELS:
            print(
                f"{actual:<9} "
                f"{confusion[actual]['positive']:>9} "
                f"{confusion[actual]['negative']:>9} "
                f"{confusion[actual]['neutral']:>8}"
            )

        print("\nCreated:")
        print(f"- {args.results_output}")
        print(f"- {args.metrics_output}")
        print(f"- {args.confusion_output}")
        return 0

    except Exception as exc:
        print(f"ERROR: {exc}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
