import { Skeleton } from '@/components/ui/skeleton';

const WEEKDAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

/**
 * Content-shaped placeholder for the deferred month grid. Mirrors
 * {@link MonthGrid}'s `px-3 py-3` frame, weekday header, 7-column `gap-px`
 * grid, and 96px day cells with `h-5` post chips so the streamed-in posts swap
 * in with zero layout shift. Used as the fallback for both month and week views
 * (the month grid is the common case).
 */
export function CalendarSkeleton() {
    return (
        <div className="px-3 py-3">
            <div className="mb-1 grid grid-cols-7 text-[10px] font-semibold tracking-[0.08em] text-muted-foreground uppercase">
                {WEEKDAYS.map((w) => (
                    <div key={w} className="px-2 pb-1.5 text-center">
                        {w}
                    </div>
                ))}
            </div>
            <div className="grid grid-cols-7 gap-px overflow-hidden rounded-lg border border-border bg-border">
                {Array.from({ length: 42 }).map((_, i) => (
                    <div key={i} className="min-h-[96px] bg-background p-1.5">
                        <div className="mb-1 flex h-[18px] items-center">
                            <Skeleton className="h-3 w-4 rounded-md" />
                        </div>
                        <div className="space-y-0.5">
                            {i % 3 === 0 && (
                                <Skeleton className="h-5 w-full rounded-sm" />
                            )}
                            {i % 5 === 0 && (
                                <Skeleton className="h-5 w-3/4 rounded-sm" />
                            )}
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
