import { useMemo, useState } from 'react';
import {
    Calendar,
    dateFnsLocalizer,
    type EventProps,
    type EventPropGetter,
    type ToolbarProps,
} from 'react-big-calendar';
import { format, getDay, parse, startOfWeek } from 'date-fns';
import { enGB } from 'date-fns/locale/en-GB';
import 'react-big-calendar/lib/css/react-big-calendar.min.css';

interface MatchEvent {
    id: string | null;
    date: string | null;
    time: string | null;
    homeClub: string | null;
    homeTeam: string | null;
    awayClub: string | null;
    awayTeam: string | null;
    score: string | null;
    divisionName: string | null;
    isHome: boolean;
}

interface CalendarEvent {
    id: string | null;
    title: string;
    start: Date;
    end: Date;
    allDay: boolean;
    resource: MatchEvent;
}

interface Props {
    events?: MatchEvent[];
}

const locales = { 'en-GB': enGB };

const localizer = dateFnsLocalizer({
    format,
    parse,
    startOfWeek,
    getDay,
    locales,
});

const MATCH_DURATION_MS = 2 * 60 * 60 * 1000;
const MIN_TIME = new Date(1970, 0, 1, 8, 0);
const MAX_TIME = new Date(1970, 0, 1, 23, 30);
const SCROLL_TIME = new Date(1970, 0, 1, 16, 0);

function parseDateTime(date: string | null, time: string | null): Date | null {
    if (!date) {
        return null;
    }

    const [year, month, day] = date.slice(0, 10).split('-').map(Number);

    if (!year || !month || !day) {
        return null;
    }

    let hours = 0;
    let minutes = 0;

    if (time && /^\d{1,2}:\d{2}/.test(time)) {
        const [parsedHours, parsedMinutes] = time.split(':').map(Number);
        hours = parsedHours;
        minutes = parsedMinutes;
    }

    return new Date(year, month - 1, day, hours, minutes);
}

function startOfToday(): Date {
    const now = new Date();

    return new Date(now.getFullYear(), now.getMonth(), now.getDate());
}

function CalendarToolbar({ label, onNavigate }: ToolbarProps<CalendarEvent>) {
    const buttonClass = 'flex size-10 items-center justify-center rounded-lg border border-gray-200 text-gray-900 transition hover:bg-gray-900/10 active:bg-gray-900/20 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/10';

    return (
        <div className="mb-4 flex flex-wrap items-center justify-between gap-4">
            <h2 className="text-base font-semibold text-gray-900 dark:text-white">{label}</h2>

            <div className="flex items-center gap-2">
                <button type="button" onClick={() => onNavigate('PREV')} aria-label="Previous week" className={buttonClass}>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={3} stroke="currentColor" aria-hidden="true" className="size-3.5">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </button>

                <button type="button" onClick={() => onNavigate('NEXT')} aria-label="Next week" className={buttonClass}>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={3} stroke="currentColor" aria-hidden="true" className="size-3.5">
                        <path strokeLinecap="round" strokeLinejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>

                <button
                    type="button"
                    onClick={() => onNavigate('TODAY')}
                    className="rounded-lg border border-gray-200 px-6 py-3 text-xs font-bold uppercase text-gray-900 transition hover:bg-gray-900/10 active:bg-gray-900/20 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/10"
                >
                    Today
                </button>
            </div>
        </div>
    );
}

function MatchEventCell({ event }: EventProps<CalendarEvent>) {
    const match = event.resource;

    return (
        <div className="min-w-0 leading-tight">
            <p className="truncate font-semibold">
                {match.homeTeam} &ndash; {match.awayTeam}
            </p>
            {match.divisionName && <p className="truncate text-[11px] opacity-90">{match.divisionName}</p>}
        </div>
    );
}

const eventPropGetter: EventPropGetter<CalendarEvent> = (event) => ({
    style: {
        backgroundColor: event.resource.isHome ? '#4f46e5' : '#6b7280',
        color: '#ffffff',
        border: 'none',
        borderRadius: '6px',
        padding: '2px 6px',
        fontSize: '0.75rem',
    },
});

export default function CalendarView({ events = [] }: Props) {
    const calendarEvents = useMemo<CalendarEvent[]>(
        () =>
            events.flatMap((event) => {
                const start = parseDateTime(event.date, event.time);

                if (!start) {
                    return [];
                }

                const allDay = !event.time;

                return [
                    {
                        id: event.id,
                        title: `${event.homeTeam ?? ''} – ${event.awayTeam ?? ''}`,
                        start,
                        end: allDay ? start : new Date(start.getTime() + MATCH_DURATION_MS),
                        allDay,
                        resource: event,
                    },
                ];
            }),
        [events],
    );

    const defaultDate = useMemo(() => {
        const today = startOfToday();
        const sorted = calendarEvents
            .map((event) => event.start)
            .sort((a, b) => a.getTime() - b.getTime());

        return sorted.find((date) => date.getTime() >= today.getTime()) ?? sorted[0] ?? today;
    }, [calendarEvents]);

    const [selected, setSelected] = useState<CalendarEvent | null>(null);

    return (
        <div className="mx-auto max-w-6xl">
            <div className="rounded-xl border border-gray-300 bg-white p-4 text-gray-700 shadow-md dark:border-white/10 dark:bg-gray-800 dark:text-gray-200">
                <div className="h-[50rem]">
                    <Calendar<CalendarEvent>
                        localizer={localizer}
                        events={calendarEvents}
                        defaultDate={defaultDate}
                        defaultView="week"
                        views={['week']}
                        culture="en-GB"
                        step={30}
                        timeslots={2}
                        min={MIN_TIME}
                        max={MAX_TIME}
                        scrollToTime={SCROLL_TIME}
                        onSelectEvent={(event) => setSelected(event)}
                        eventPropGetter={eventPropGetter}
                        components={{ toolbar: CalendarToolbar, event: MatchEventCell }}
                        messages={{
                            today: 'Today',
                            previous: 'Previous',
                            next: 'Next',
                            week: 'Week',
                            showMore: (count) => `+${count} more`,
                        }}
                        style={{ height: '100%' }}
                    />
                </div>
            </div>

            {selected && (
                <div className="mt-4 rounded-xl border border-gray-300 bg-white p-4 shadow-xs dark:border-white/10 dark:bg-gray-800">
                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <h3 className="text-base font-semibold text-gray-900 dark:text-white">
                                {selected.resource.divisionName ?? 'Match'}
                            </h3>
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {selected.start.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long' })}
                                {selected.resource.time ? ` · ${selected.resource.time}` : ''}
                            </p>
                        </div>

                        <div className="flex items-center gap-2">
                            <span
                                className={`inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-xs font-medium ${selected.resource.isHome
                                        ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300'
                                        : 'bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-300'
                                    }`}
                            >
                                {selected.resource.isHome ? 'Home' : 'Away'}
                            </span>
                            {selected.resource.id && (
                                <a
                                    href={`/match/${selected.resource.id}`}
                                    className="rounded-md bg-indigo-600 px-2.5 py-1 text-xs font-semibold text-white transition hover:bg-indigo-500"
                                >
                                    View match
                                </a>
                            )}
                            <button
                                type="button"
                                onClick={() => setSelected(null)}
                                aria-label="Close"
                                className="text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-200"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor" aria-hidden="true" className="size-5">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div className="mt-4 flex flex-wrap items-center gap-x-3 gap-y-2">
                        <span className="text-sm font-medium text-gray-900 dark:text-white">{selected.resource.homeTeam}</span>
                        {selected.resource.score ? (
                            <span className="inline-flex items-center rounded bg-gray-100 px-2 py-0.5 font-mono text-xs font-semibold text-gray-700 dark:bg-white/10 dark:text-gray-200">
                                {selected.resource.score}
                            </span>
                        ) : (
                            <span className="text-xs font-normal text-gray-400">vs</span>
                        )}
                        <span className="text-sm font-medium text-gray-900 dark:text-white">{selected.resource.awayTeam}</span>
                    </div>
                </div>
            )}
        </div>
    );
}
