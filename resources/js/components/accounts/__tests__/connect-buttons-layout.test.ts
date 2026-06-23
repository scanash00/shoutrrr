import { describe, expect, it } from 'vitest';

import { ADVANCED_SERVICE_URL_TRIGGER_CLASS } from '../connect-buttons';

describe('Bluesky connect dialog layout', () => {
    it('marks the advanced service URL trigger as a visible expandable control', () => {
        expect(ADVANCED_SERVICE_URL_TRIGGER_CLASS).toContain(
            '[&[data-state=open]_svg]:rotate-180',
        );
    });
});
