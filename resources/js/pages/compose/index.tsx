import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, PenLine } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { PostPageActions } from '@/pages/posts/post-page-actions';
import { dashboard } from '@/routes';
import { index as postsRoute } from '@/routes/posts';

import Composer from './Composer';
import { firstLineTitle } from './composer-state';
import type { ComposePageProps } from './types';

export default function ComposePage({
    post,
    accounts,
    sets,
    limits,
}: ComposePageProps) {
    const title = firstLineTitle(post?.base_text ?? '');

    return (
        <>
            <Head title="Compose" />
            <div className="mx-auto w-full max-w-6xl px-4 pt-6 pb-16 sm:px-6">
                <div className="sticky top-0 z-10 mb-5 flex items-center gap-2 border-b border-border bg-background/85 px-2 py-2 backdrop-blur-md">
                    <Button
                        asChild
                        variant="ghost"
                        size="sm"
                        className="h-8 gap-1.5 px-2 text-muted-foreground hover:text-foreground"
                    >
                        <Link href={postsRoute().url}>
                            <ArrowLeft className="size-4" />
                            Posts
                        </Link>
                    </Button>
                    <div className="h-4 w-px bg-border" aria-hidden />
                    <div className="flex min-w-0 flex-1 items-center gap-1.5">
                        <PenLine
                            className="size-3.5 shrink-0 text-muted-foreground"
                            aria-hidden
                        />
                        <span className="truncate text-[13px] font-medium tracking-tight">
                            {title || 'Untitled draft'}
                        </span>
                    </div>
                    {post && <PostPageActions post={post} />}
                </div>

                <Composer
                    post={post}
                    accounts={accounts}
                    sets={sets}
                    limits={limits}
                />
            </div>
        </>
    );
}

ComposePage.layout = {
    breadcrumbs: [
        {
            title: 'Compose',
            href: dashboard().url,
        },
    ],
};
