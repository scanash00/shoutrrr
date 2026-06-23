import { describe, expect, it } from 'vitest';

import { ACCOUNT_CARD_ACTIONS_CLASS } from '../account-card';

describe('account card layout', () => {
    it('lets management actions wrap inside the card', () => {
        expect(ACCOUNT_CARD_ACTIONS_CLASS).toContain('flex-wrap');
    });
});
