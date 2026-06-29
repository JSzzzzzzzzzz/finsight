#!/usr/bin/env python3
"""
Collect a balanced candidate dataset of cryptocurrency-news headlines from the
same CryptoCompare News API used by FinSight.

This one-reviewer version outputs one human ground-truth field:
    researcher_label

Usage from the FinSight project root:
    python collect_cryptocompare_dataset_one_reviewer.py
"""

from __future__ import annotations

import argparse
import csv
import json
import os
import re
import sys
import time
import urllib.parse
import urllib.request
from datetime import datetime, timezone
from pathlib import Path
from typing import Dict, Iterable, List, Optional

API_URL = "https://min-api.cryptocompare.com/data/v2/news/"

POSITIVE_TERMS = {
    "adoption", "adopts", "approval", "approved", "approves", "gain", "gains",
    "gained", "growth", "grows", "surge", "surges", "rally", "rallies",
    "rise", "rises", "rose", "record high", "breakout", "partnership",
    "launch", "launched", "upgrade", "expansion", "investment", "invests",
    "institutional demand", "inflows", "recovery", "recovers", "bullish",
    "success", "wins", "milestone", "boost", "beats expectations"
}

NEGATIVE_TERMS = {
    "decline", "declines", "declined", "drop", "drops", "fell", "falls",
    "loss", "losses", "hack", "hacked", "breach", "fraud", "scam",
    "lawsuit", "sued", "ban", "bans", "banned", "restriction", "crackdown",
    "rejection", "rejected", "outflows", "liquidation", "liquidations",
    "bearish", "collapse", "crash", "slump", "sell-off", "investigation",
    "charges", "charged", "fine", "fined", "exploit", "stolen", "warning"
}

ASSET_KEYWORDS = {
    "BTC": ["bitcoin", "btc", "xbt"],
    "ETH": ["ethereum", "ether", "eth"],
    "XRP": ["xrp", "ripple"],
    "ADA": ["cardano", "ada"],
    "LINK": ["chainlink", "link"],
    "LTC": ["litecoin", "ltc"],
    "SOL": ["solana", "sol"],
    "DOGE": ["dogecoin", "doge"],
}


def read_env_file(path: Path) -> Dict[str, str]:
    values: Dict[str, str] = {}
    if not path.exists():
        return values

    for raw_line in path.read_text(encoding="utf-8", errors="ignore").splitlines():
        line = raw_line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue

        key, value = line.split("=", 1)
        values[key.strip()] = value.strip().strip('"').strip("'")

    return values


def find_api_key(env_file: Optional[str]) -> str:
    key = os.getenv("CRYPTOCOMPARE_API_KEY", "").strip()
    if key:
        return key

    candidates = [Path(env_file)] if env_file else []
    candidates.extend([Path(".env"), Path.cwd() / ".env"])

    for candidate in candidates:
        values = read_env_file(candidate)
        key = values.get("CRYPTOCOMPARE_API_KEY", "").strip()
        if key:
            return key

    raise RuntimeError(
        "CRYPTOCOMPARE_API_KEY was not found. Run this script from the FinSight "
        "project root or pass --env-file."
    )


def normalise_title(title: str) -> str:
    return re.sub(r"\s+", " ", title).strip()


def dedupe_key(title: str) -> str:
    return re.sub(r"[^a-z0-9]+", " ", title.lower()).strip()


def detect_asset(title: str) -> str:
    text = title.lower()
    found = []

    for symbol, keywords in ASSET_KEYWORDS.items():
        if any(re.search(rf"\b{re.escape(keyword)}\b", text) for keyword in keywords):
            found.append(symbol)

    return ",".join(found) if found else "GENERAL"


def provisional_label(title: str) -> str:
    text = title.lower()
    positive = sum(1 for term in POSITIVE_TERMS if term in text)
    negative = sum(1 for term in NEGATIVE_TERMS if term in text)

    if positive > negative:
        return "positive"
    if negative > positive:
        return "negative"
    return "neutral"


def fetch_page(api_key: str, before_timestamp: Optional[int] = None) -> List[dict]:
    params = {"lang": "EN", "api_key": api_key}
    if before_timestamp is not None:
        params["lTs"] = str(before_timestamp)

    url = API_URL + "?" + urllib.parse.urlencode(params)
    request = urllib.request.Request(
        url,
        headers={"User-Agent": "FinSight-FYP-Validation/1.0"}
    )

    with urllib.request.urlopen(request, timeout=30) as response:
        payload = json.loads(response.read().decode("utf-8"))

    if payload.get("Response") == "Error":
        raise RuntimeError(payload.get("Message", "CryptoCompare API error"))

    data = payload.get("Data", [])
    if not isinstance(data, list):
        raise RuntimeError("Unexpected CryptoCompare response format")

    return data


def collect_articles(api_key: str, target_candidates: int, max_pages: int) -> List[dict]:
    rows: List[dict] = []
    seen = set()
    before: Optional[int] = None

    for _ in range(max_pages):
        items = fetch_page(api_key, before)
        if not items:
            break

        oldest = None

        for item in items:
            title = normalise_title(str(item.get("title", "")))
            if not title:
                continue

            key = dedupe_key(title)
            if not key or key in seen:
                continue
            seen.add(key)

            published_on = item.get("published_on")
            if isinstance(published_on, int):
                published_at = datetime.fromtimestamp(
                    published_on, tz=timezone.utc
                ).strftime("%Y-%m-%d %H:%M:%S UTC")
                oldest = published_on if oldest is None else min(oldest, published_on)
            else:
                published_at = ""

            rows.append({
                "headline": title,
                "source": str(item.get("source", "")),
                "published_at": published_at,
                "crypto_asset": detect_asset(title),
                "article_url": str(item.get("url", "")),
                "provisional_label": provisional_label(title),
            })

        if len(rows) >= target_candidates:
            break
        if oldest is None:
            break

        before = oldest - 1
        time.sleep(0.35)

    return rows


def write_csv(path: Path, rows: Iterable[dict]) -> None:
    fieldnames = [
        "id", "headline", "source", "published_at", "crypto_asset", "article_url",
        "provisional_label", "researcher_label", "notes", "finbert_prediction",
        "positive_probability", "negative_probability", "neutral_probability",
        "correct"
    ]

    with path.open("w", encoding="utf-8-sig", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=fieldnames)
        writer.writeheader()

        for idx, row in enumerate(rows, start=1):
            writer.writerow({
                "id": idx,
                "headline": row.get("headline", ""),
                "source": row.get("source", ""),
                "published_at": row.get("published_at", ""),
                "crypto_asset": row.get("crypto_asset", ""),
                "article_url": row.get("article_url", ""),
                "provisional_label": row.get("provisional_label", ""),
                "researcher_label": "",
                "notes": "",
                "finbert_prediction": "",
                "positive_probability": "",
                "negative_probability": "",
                "neutral_probability": "",
                "correct": "",
            })


def balanced_selection(rows: List[dict], per_class: int) -> List[dict]:
    groups = {"positive": [], "negative": [], "neutral": []}

    for row in rows:
        groups[row["provisional_label"]].append(row)

    shortages = {
        label: per_class - len(group)
        for label, group in groups.items()
        if len(group) < per_class
    }

    if shortages:
        details = ", ".join(f"{label}: short by {count}" for label, count in shortages.items())
        raise RuntimeError(
            "Not enough provisionally balanced headlines were collected "
            f"({details}). Increase --candidate-count or --max-pages."
        )

    selected = []
    for index in range(per_class):
        selected.append(groups["positive"][index])
        selected.append(groups["negative"][index])
        selected.append(groups["neutral"][index])

    return selected


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--env-file", default=None)
    parser.add_argument("--per-class", type=int, default=20)
    parser.add_argument("--candidate-count", type=int, default=180)
    parser.add_argument("--max-pages", type=int, default=12)
    parser.add_argument("--output", default="finbert_validation_dataset_one_reviewer.csv")
    parser.add_argument("--candidates-output", default="cryptocompare_candidates_one_reviewer.csv")
    args = parser.parse_args()

    try:
        api_key = find_api_key(args.env_file)
        rows = collect_articles(api_key, args.candidate_count, args.max_pages)

        if not rows:
            raise RuntimeError("No CryptoCompare news records were returned.")

        write_csv(Path(args.candidates_output), rows)
        selected = balanced_selection(rows, args.per_class)
        write_csv(Path(args.output), selected)

        print(f"Created {args.candidates_output} with {len(rows)} candidates.")
        print(f"Created {args.output} with {len(selected)} selected headlines.")
        print(
            "Next: manually complete researcher_label before running FinBERT. "
            "The provisional label is only a collection aid."
        )
        return 0

    except Exception as exc:
        print(f"ERROR: {exc}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
