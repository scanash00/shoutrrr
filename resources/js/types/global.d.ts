import type { Auth } from '@/types/auth';
import type { FlashData, WorkspacesData } from '@/types/workspace';

declare module 'react' {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            workspaces: WorkspacesData;
            flash: FlashData;
            socialite: { providers: string[] };
            [key: string]: unknown;
        };
    }
}
