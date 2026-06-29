import test from 'node:test';
import assert from 'node:assert/strict';

import {
    calculateRiskSimulation,
    calculateMarketMoodIndex,
    classifyMarketMood,
    shouldShowRiskNudge,
} from '../../resources/js/Utils/finSightCalculations.js';

test('UT-06: risk simulation calculates 0%, 15%, and 100% drops', () => {
    assert.deepEqual(
        calculateRiskSimulation(10000, 0),
        {
            currentValue: 10000,
            dropPercent: 0,
            estimatedLoss: 0,
            projectedValue: 10000,
        },
    );

    assert.deepEqual(
        calculateRiskSimulation(10000, 15),
        {
            currentValue: 10000,
            dropPercent: 15,
            estimatedLoss: 1500,
            projectedValue: 8500,
        },
    );

    assert.deepEqual(
        calculateRiskSimulation(10000, 100),
        {
            currentValue: 10000,
            dropPercent: 100,
            estimatedLoss: 10000,
            projectedValue: 0,
        },
    );
});

test('UT-09: market mood boundaries are classified correctly', () => {
    assert.equal(classifyMarketMood(39), 'Bearish');
    assert.equal(classifyMarketMood(40), 'Neutral');
    assert.equal(classifyMarketMood(60), 'Neutral');
    assert.equal(classifyMarketMood(61), 'Bullish');
});

test('UT-09: market mood formula produces the expected score', () => {
    assert.equal(calculateMarketMoodIndex(0.2, 0.4), 40);
    assert.equal(calculateMarketMoodIndex(0.6, 0.2), 70);
    assert.equal(calculateMarketMoodIndex(0.2, 0.6), 30);
});

test('UT-10: risk nudge appears only at score 65 or above with a negative change', () => {
    assert.equal(shouldShowRiskNudge(64, -100), false);
    assert.equal(shouldShowRiskNudge(65, -0.01), true);
    assert.equal(shouldShowRiskNudge(80, 0), false);
    assert.equal(shouldShowRiskNudge(80, 100), false);
});
