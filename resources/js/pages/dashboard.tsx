import { Deferred, Head, usePage } from '@inertiajs/react';

import { RecentFeed } from '@/components/recent-feed';
import { RecentFeedSkeleton } from '@/components/skeletons/recent-feed-skeleton';
import Composer from '@/pages/compose/Composer';
import type { PostRowData } from '@/pages/posts/post-row';
import { dashboard } from '@/routes';

type Props = {
    posts?: PostRowData[];
};

function timeGreeting(): string {
    const hour = new Date().getHours();
    if (hour < 5) {
        return 'Working late';
    }
    if (hour < 12) {
        return 'Good morning';
    }
    if (hour < 18) {
        return 'Good afternoon';
    }

    return 'Good evening';
}

export default function Dashboard({ posts }: Props) {
    const page = usePage();
    const { auth, shell } = page.props;
    const firstName = (auth.user?.name ?? '').split(/\s+/)[0] || 'there';

    // A calendar slot click opens the composer here with a pre-set schedule time.
    const initialScheduleAt = new URL(
        page.url,
        'http://localhost',
    ).searchParams.get('schedule_at');

    return (
        <>
            <Head title="Dashboard" />
            <div className="mx-auto w-full max-w-6xl px-4 pt-6 pb-16 sm:px-6">
                <h1 className="text-[26px] leading-tight font-semibold tracking-tight">
                    {timeGreeting()},{' '}
                    <span className="bg-gradient-to-br from-primary to-primary/60 bg-clip-text text-transparent">
                        {firstName}
                    </span>
                </h1>
                <p className="mt-1.5 mb-7 text-[13.5px] tracking-tight text-muted-foreground">
                    Write something new — it autosaves as you go.
                </p>

                <Composer
                    post={null}
                    accounts={shell.accounts}
                    sets={shell.sets}
                    limits={shell.limits}
                    initialScheduleAt={initialScheduleAt}
                />

                <Deferred data="posts" fallback={<RecentFeedSkeleton />}>
                    <RecentFeed posts={posts ?? []} />
                </Deferred>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
