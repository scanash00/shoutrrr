import { Skeleton } from '@/components/ui/skeleton';

/**
 * Content-shaped placeholder for the deferred queue editor body. Mirrors the
 * `ScheduleEditor` cadence overview card and the 7-column week board (matching
 * its `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7` breakpoints,
 * `rounded-xl border p-3` day cells, and `h-[26px]` time pills) so the streamed
 * editor swaps in with zero layout shift. The slots-independent header lives on
 * the page above this fallback and paints immediately.
 */
export function QueueSkeleton() {
    return (
        <div className="space-y-5">
            {/* Cadence overview */}
            <section className="flex flex-col gap-5 rounded-xl border border-border bg-card p-4 sm:flex-row sm:items-center sm:justify-between sm:gap-8 sm:p-5">
                <div className="flex items-end gap-2">
                    {Array.from({ length: 7 }).map((_, i) => (
                        <div
                            key={i}
                            className="flex flex-1 flex-col items-center gap-1.5"
                        >
                            <div className="h-[14px]" />
                            <div className="flex h-10 w-full items-end justify-center">
                                <Skeleton className="h-5 w-2.5 rounded-full" />
                            </div>
                            <Skeleton className="h-3 w-3 rounded-sm" />
                        </div>
                    ))}
                </div>

                <div className="flex items-center gap-6 sm:gap-8">
                    {Array.from({ length: 3 }).map((_, i) => (
                        <div key={i} className="flex flex-col gap-1.5">
                            <Skeleton className="h-[22px] w-10 rounded-md" />
                            <Skeleton className="h-3 w-16 rounded-sm" />
                        </div>
                    ))}
                </div>
            </section>

            {/* Week board */}
            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
                {Array.from({ length: 7 }).map((_, day) => (
                    <div
                        key={day}
                        className="flex flex-col gap-2 rounded-xl border border-border bg-card p-3"
                    >
                        <div className="flex items-center justify-between">
                            <Skeleton className="h-3.5 w-8 rounded-sm" />
                        </div>
                        <div className="flex flex-col gap-1.5">
                            {Array.from({ length: (day % 3) + 1 }).map(
                                (__, j) => (
                                    <Skeleton
                                        key={j}
                                        className="h-[26px] w-full rounded-md"
                                    />
                                ),
                            )}
                            <Skeleton className="h-[26px] w-full rounded-md opacity-60" />
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
