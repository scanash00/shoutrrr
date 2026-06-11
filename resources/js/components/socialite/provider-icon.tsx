import { Globe } from 'lucide-react';
import type { ReactElement, SVGProps } from 'react';

import GoogleIcon from '@/components/socialite/icons/google-icon';

const ICONS: Record<string, (props: SVGProps<SVGSVGElement>) => ReactElement> =
    {
        google: GoogleIcon,
    };

type Props = SVGProps<SVGSVGElement> & {
    provider: string;
};

export default function ProviderIcon({ provider, ...props }: Props) {
    const Icon = ICONS[provider];

    if (!Icon) {
        return <Globe {...props} />;
    }

    return <Icon {...props} />;
}
