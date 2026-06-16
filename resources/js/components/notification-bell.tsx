import { Link } from '@inertiajs/react';
import { Bell } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';

export type AppNotification = {
    id: string;
    title: string;
    body?: string;
    href?: string;
    read: boolean;
    /** Human-readable relative time, e.g. "2h ago". */
    timeLabel: string;
};

/*
 * No notifications backend exists yet — this is the UI shell. When it lands,
 * source `notifications` from shared Inertia props (usePage().props) and wire
 * `Mark all read` + per-row activation to their endpoints. Everything else
 * (badge, popover, list rows, empty state) is already in place.
 */
const notifications: AppNotification[] = [];

export function NotificationBell() {
    const unread = notifications.filter((n) => !n.read).length;

    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button
                    variant="ghost"
                    size="icon"
                    className="relative size-8 text-muted-foreground"
                    aria-label={
                        unread > 0
                            ? `Notifications (${unread} unread)`
                            : 'Notifications'
                    }
                >
                    <Bell className="size-4" />
                    {unread > 0 && (
                        <span className="absolute -top-0.5 -right-0.5 grid h-4 min-w-4 place-items-center rounded-full bg-destructive px-1 text-[10px] font-medium text-white tabular-nums">
                            {unread > 9 ? '9+' : unread}
                        </span>
                    )}
                </Button>
            </PopoverTrigger>
            <PopoverContent align="end" className="w-80 gap-0 p-0">
                <div className="flex items-center justify-between border-b border-border px-3 py-2">
                    <span className="text-[13px] font-semibold">
                        Notifications
                    </span>
                    <Button
                        variant="ghost"
                        size="sm"
                        className="h-6 px-2 text-[12px]"
                        disabled={unread === 0}
                    >
                        Mark all read
                    </Button>
                </div>

                <div className="max-h-96 overflow-y-auto">
                    {notifications.length === 0 ? (
                        <div className="flex flex-col items-center gap-2 px-3 py-10 text-center">
                            <span className="grid size-9 place-items-center rounded-full bg-muted text-muted-foreground">
                                <Bell className="size-4" />
                            </span>
                            <p className="text-[12.5px] text-muted-foreground">
                                {"You're all caught up"}
                            </p>
                        </div>
                    ) : (
                        notifications.map((notification) => (
                            <NotificationRow
                                key={notification.id}
                                notification={notification}
                            />
                        ))
                    )}
                </div>
            </PopoverContent>
        </Popover>
    );
}

function NotificationRow({ notification }: { notification: AppNotification }) {
    const body = (
        <div className="flex gap-2.5 px-3 py-2.5 text-left transition-colors hover:bg-muted/50">
            <span
                aria-hidden
                className={cn(
                    'mt-1.5 size-1.5 shrink-0 rounded-full',
                    notification.read ? 'bg-transparent' : 'bg-primary',
                )}
            />
            <div className="min-w-0 flex-1">
                <p className="truncate text-[13px] font-medium text-foreground">
                    {notification.title}
                </p>
                {notification.body && (
                    <p className="mt-0.5 line-clamp-2 text-[12px] text-muted-foreground">
                        {notification.body}
                    </p>
                )}
                <p className="mt-1 text-[11px] text-muted-foreground/70">
                    {notification.timeLabel}
                </p>
            </div>
        </div>
    );

    if (notification.href) {
        return (
            <Link
                href={notification.href}
                className="block border-b border-border last:border-b-0"
            >
                {body}
            </Link>
        );
    }

    return <div className="border-b border-border last:border-b-0">{body}</div>;
}
