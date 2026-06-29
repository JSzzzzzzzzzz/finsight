/**
 * Pure calculation helpers used by the FinSight Vue components.
 * Keeping them separate makes the implemented logic directly unit-testable.
 */

export function calculateRiskSimulation(currentValue, dropPercent) {
    const safeCurrentValue = Number(currentValue ?? 0);
    const safeDropPercent = Number(dropPercent ?? 0);

    const estimatedLoss =
        safeCurrentValue * (safeDropPercent / 100);

    const projectedValue =
        safeCurrentValue - estimatedLoss;

    return {
        currentValue: safeCurrentValue,
        dropPercent: safeDropPercent,
        estimatedLoss,
        projectedValue,
    };
}

export function calculateMarketMoodIndex(
    positiveAverage,
    negativeAverage,
) {
    const positive = Number(positiveAverage ?? 0);
    const negative = Number(negativeAverage ?? 0);

    return Math.round(
        ((positive - negative + 1) / 2) * 100,
    );
}

export function classifyMarketMood(moodIndex) {
    const score = Number(moodIndex);

    if (score >= 61) {
        return 'Bullish';
    }

    if (score <= 39) {
        return 'Bearish';
    }

    return 'Neutral';
}

export function shouldShowRiskNudge(
    riskScore,
    portfolioChangeAmount,
) {
    return (
        Number(riskScore) >= 65
        && Number(portfolioChangeAmount) < 0
    );
}
