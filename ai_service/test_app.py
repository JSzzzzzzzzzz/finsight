from types import SimpleNamespace

import pytest
import torch

import app as ai_app


class DummyTokenizer:
    def __call__(
        self,
        text,
        return_tensors="pt",
        truncation=True,
        padding=True,
        max_length=512,
    ):
        return {
            "input_ids": torch.tensor([[1]]),
            "attention_mask": torch.tensor([[1]]),
        }


class DummyModel:
    def __init__(self, logits):
        self.logits = list(logits)

    def __call__(self, **kwargs):
        current = self.logits.pop(0)

        return SimpleNamespace(
            logits=torch.tensor(
                [current],
                dtype=torch.float32,
            )
        )


@pytest.mark.parametrize(
    ("average_negative", "expected_score", "expected_level"),
    [
        (0.20, 20, "Low"),
        (0.34, 34, "Low"),
        (0.35, 35, "Medium"),
        (0.59, 59, "Medium"),
        (0.60, 60, "High"),
    ],
)
def test_ut_08_risk_score_boundaries(
    average_negative,
    expected_score,
    expected_level,
):
    score, level = ai_app.calculate_risk_level(
        average_negative
    )

    assert score == expected_score
    assert level == expected_level


def test_ut_08_empty_headline_list_returns_http_400():
    client = ai_app.app.test_client()

    response = client.post(
        "/analyze-sentiment",
        json={"texts": []},
    )

    assert response.status_code == 400
    assert response.get_json() == {
        "error": "No texts provided"
    }


def test_ut_08_sentiment_labels_probabilities_and_averages(
    monkeypatch,
):
    monkeypatch.setattr(
        ai_app,
        "tokenizer",
        DummyTokenizer(),
    )

    monkeypatch.setattr(
        ai_app,
        "model",
        DummyModel([
            [5.0, 0.0, 0.0],  # positive
            [0.0, 5.0, 0.0],  # negative
            [0.0, 0.0, 5.0],  # neutral
        ]),
    )

    client = ai_app.app.test_client()

    response = client.post(
        "/analyze-sentiment",
        json={
            "texts": [
                "Positive headline",
                "Negative headline",
                "Neutral headline",
            ]
        },
    )

    assert response.status_code == 200

    data = response.get_json()

    assert [
        result["sentiment"]
        for result in data["results"]
    ] == [
        "positive",
        "negative",
        "neutral",
    ]

    assert len(data["results"]) == 3

    for result in data["results"]:
        total = (
            result["positive"]
            + result["negative"]
            + result["neutral"]
        )

        assert total == pytest.approx(
            1.0,
            abs=0.001,
        )

    assert data["average"]["positive"] == pytest.approx(
        1 / 3,
        abs=0.01,
    )

    assert data["average"]["negative"] == pytest.approx(
        1 / 3,
        abs=0.01,
    )

    assert data["average"]["neutral"] == pytest.approx(
        1 / 3,
        abs=0.01,
    )

    assert data["risk_score"] == 33
    assert data["risk_level"] == "Low"
