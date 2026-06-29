from flask import Flask, request, jsonify
from flask_cors import CORS
from transformers import AutoTokenizer, AutoModelForSequenceClassification
import torch

app = Flask(__name__)
CORS(app)

MODEL_NAME = "ProsusAI/finbert"

tokenizer = AutoTokenizer.from_pretrained(MODEL_NAME)
model = AutoModelForSequenceClassification.from_pretrained(MODEL_NAME)

labels = ["positive", "negative", "neutral"]


def calculate_risk_level(avg_negative):
    risk_score = round(avg_negative * 100)

    if risk_score >= 60:
        risk_level = "High"
    elif risk_score >= 35:
        risk_level = "Medium"
    else:
        risk_level = "Low"

    return risk_score, risk_level


@app.route("/analyze-sentiment", methods=["POST"])
def analyze_sentiment():
    data = request.get_json()

    texts = data.get("texts", [])

    if not texts:
        return jsonify({
            "error": "No texts provided"
        }), 400

    results = []

    total_positive = 0
    total_negative = 0
    total_neutral = 0

    for text in texts:
        inputs = tokenizer(
            text,
            return_tensors="pt",
            truncation=True,
            padding=True,
            max_length=512
        )

        with torch.no_grad():
            outputs = model(**inputs)
            probabilities = torch.nn.functional.softmax(
                outputs.logits,
                dim=-1
            )[0]

        positive = float(probabilities[0])
        negative = float(probabilities[1])
        neutral = float(probabilities[2])

        total_positive += positive
        total_negative += negative
        total_neutral += neutral

        max_index = torch.argmax(probabilities).item()

        results.append({
            "text": text,
            "sentiment": labels[max_index],
            "positive": round(positive, 4),
            "negative": round(negative, 4),
            "neutral": round(neutral, 4)
        })

    count = len(texts)

    avg_positive = total_positive / count
    avg_negative = total_negative / count
    avg_neutral = total_neutral / count

    # Risk score: higher negative sentiment = higher risk
    risk_score, risk_level = calculate_risk_level(avg_negative)

    return jsonify({
        "average": {
            "positive": round(avg_positive, 4),
            "negative": round(avg_negative, 4),
            "neutral": round(avg_neutral, 4)
        },
        "risk_score": risk_score,
        "risk_level": risk_level,
        "results": results
    })


@app.route("/", methods=["GET"])
def health_check():
    return jsonify({
        "status": "online",
        "service": "FinBERT AI Service"
    }), 200


if __name__ == "__main__":
    app.run(port=5001, debug=True)