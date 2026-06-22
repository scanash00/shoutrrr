import { describe, expect, it } from 'vitest';

import { formatFollowerTooltipDate } from '../follower-chart';

describe('follower chart tooltip', () => {
    it('formats the x-axis date from the tooltip payload', () => {
        const timestamp = new Date('2026-06-22T12:00:00.000Z').getTime();

        expect(
            formatFollowerTooltipDate('Andras Bacsai', [
                { payload: { date: timestamp } },
            ]),
        ).toBe('Jun 22, 2026');
    });
});
