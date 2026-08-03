const COLOR_MAP = {
    amber: { bg: 'bg-amber-500/10', text: 'text-amber-400', dot: 'bg-amber-400' },
    teal: { bg: 'bg-teal-500/10', text: 'text-teal-400', dot: 'bg-teal-400' },
    red: { bg: 'bg-red-500/10', text: 'text-red-400', dot: 'bg-red-400' },
};

export default function StatusPill({ color, label }) {
    const c = COLOR_MAP[color] ?? COLOR_MAP.amber;

    return (
        <span className={`inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-semibold ${c.bg} ${c.text}`}>
            <span className={`h-1.5 w-1.5 rounded-full ${c.dot}`} />
            {label}
        </span>
    );
}