import { Head, Link } from '@inertiajs/react';

const COLOR_MAP = {
    amber: { bg: 'bg-amber-500/10', text: 'text-amber-400', dot: 'bg-amber-400' },
    teal: { bg: 'bg-teal-500/10', text: 'text-teal-400', dot: 'bg-teal-400' },
    red: { bg: 'bg-red-500/10', text: 'text-red-400', dot: 'bg-red-400' },
};

export default function Index({ orders }) {
    return (
        <>
            <Head title="รายการคำสั่งซื้อทั้งหมด" />

            <div className="min-h-screen bg-slate-950 p-6">
                <div className="mx-auto max-w-2xl">
                    <h1 className="mb-6 text-xl font-bold text-slate-100">รายการคำสั่งซื้อทั้งหมด</h1>

                    <div className="space-y-3">
                        {orders.map((order) => {
                            const c = COLOR_MAP[order.color] ?? COLOR_MAP.amber;
                            return (
                                <Link
                                    key={order.id}
                                    href={route('orders.show', order.id)}
                                    className="flex items-center justify-between rounded-xl border border-slate-800 bg-slate-900 p-4 transition hover:border-slate-700"
                                >
                                    <div>
                                        <div className="font-mono text-xs text-slate-500">ORDER #{order.id}</div>
                                        <div className="mt-1 text-sm font-semibold text-slate-100">
                                            {order.customer_name} — ฿{Number(order.amount).toLocaleString('th-TH', { minimumFractionDigits: 2 })}
                                        </div>
                                    </div>
                                    <span className={`inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold ${c.bg} ${c.text}`}>
                                        <span className={`h-1.5 w-1.5 rounded-full ${c.dot}`} />
                                        {order.label}
                                    </span>
                                </Link>
                            );
                        })}
                    </div>
                </div>
            </div>
        </>
    );
}