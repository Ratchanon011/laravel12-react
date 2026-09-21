import { useCallback, useEffect, useState } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Alert from '@/Components/Sales/Alert';
import StatusBadge from '@/Components/Sales/StatusBadge';
import api, { errorMessage } from '@/sales/api';
import { STATUS, STATUS_ORDER, NEXT_STATUS, NEXT_ACTION, money, dateTH } from '@/sales/format';

const statusFromUrl = (url) => {
    const s = new URLSearchParams((url ?? '').split('?')[1] ?? '').get('status');
    return STATUS_ORDER.includes(s) ? s : '';
};

export default function Index() {
    const { url } = usePage();
    const [orders, setOrders] = useState([]);
    const [meta, setMeta] = useState(null);
    const [summary, setSummary] = useState(null);
    const [status, setStatus] = useState(() => statusFromUrl(url));
    const [search, setSearch] = useState('');
    const [query, setQuery] = useState(''); // ค่า search ที่ผ่าน debounce แล้ว
    const [page, setPage] = useState(1);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [notice, setNotice] = useState('');

    // debounce การค้นหา
    useEffect(() => {
        const t = setTimeout(() => {
            setQuery(search.trim());
            setPage(1);
        }, 350);
        return () => clearTimeout(t);
    }, [search]);

    const load = useCallback(async () => {
        setLoading(true);
        try {
            const [list, sum] = await Promise.all([
                api.get('/orders', { params: { status: status || undefined, search: query || undefined, page } }),
                api.get('/summary'),
            ]);
            setOrders(list.data.data);
            setMeta(list.data.meta);
            setSummary(sum.data);
            setError('');
        } catch (e) {
            setError(errorMessage(e));
        } finally {
            setLoading(false);
        }
    }, [status, query, page]);

    useEffect(() => {
        load();
    }, [load]);

    const changeStatus = (value) => {
        setStatus(value);
        setPage(1);
    };

    const advance = async (order) => {
        const next = NEXT_STATUS[order.status];
        if (!next) return;
        try {
            await api.patch(`/orders/${order.id}/status`, { status: next });
            setNotice(`อัปเดต ${order.order_number} เป็น ${STATUS[next].label} แล้ว`);
            load();
        } catch (e) {
            setError(errorMessage(e));
        }
    };

    const remove = async (order) => {
        if (!window.confirm(`ต้องการลบใบสั่งซื้อ ${order.order_number} ใช่หรือไม่?`)) return;
        try {
            await api.delete(`/orders/${order.id}`);
            setNotice(`ลบ ${order.order_number} เรียบร้อยแล้ว`);
            if (orders.length === 1 && page > 1) setPage(page - 1);
            else load();
        } catch (e) {
            setError(errorMessage(e));
        }
    };

    const tabs = [{ key: '', label: 'ทั้งหมด', count: summary?.total }].concat(
        STATUS_ORDER.map((k) => ({ key: k, label: STATUS[k].label, count: summary?.[k] })),
    );

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold text-gray-800">ใบสั่งซื้อ</h2>
                    <Link href="/sales/orders/create" className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                        + สร้างใบสั่งซื้อ
                    </Link>
                </div>
            }
        >
            <Head title="ใบสั่งซื้อ" />

            <Alert type="success" onClose={() => setNotice('')}>{notice}</Alert>
            <Alert onClose={() => setError('')}>{error}</Alert>

            <div className="rounded-lg bg-white shadow-sm">
                <div className="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-4 py-3">
                    <div className="flex flex-wrap gap-2">
                        {tabs.map((t) => (
                            <button
                                key={t.key || 'all'}
                                type="button"
                                onClick={() => changeStatus(t.key)}
                                className={`rounded-full px-3 py-1 text-sm font-medium ${
                                    status === t.key ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                }`}
                            >
                                {t.label}
                                {t.count !== undefined && <span className="ml-1.5 opacity-80">{t.count}</span>}
                            </button>
                        ))}
                    </div>
                    <input
                        type="search"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="ค้นหาเลขที่ออเดอร์ / ชื่อลูกค้า"
                        className="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-72"
                    />
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200 text-sm">
                        <thead className="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th className="px-4 py-3">เลขที่</th>
                                <th className="px-4 py-3">ลูกค้า</th>
                                <th className="px-4 py-3">วันที่</th>
                                <th className="px-4 py-3 text-right">ยอดรวม</th>
                                <th className="px-4 py-3">สถานะ</th>
                                <th className="px-4 py-3 text-right">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {loading && orders.length === 0 && (
                                <tr>
                                    <td colSpan={6} className="px-4 py-10 text-center text-gray-400">กำลังโหลด...</td>
                                </tr>
                            )}
                            {!loading && orders.length === 0 && (
                                <tr>
                                    <td colSpan={6} className="px-4 py-10 text-center text-gray-400">ไม่พบใบสั่งซื้อ</td>
                                </tr>
                            )}
                            {orders.map((o) => (
                                <tr key={o.id} className="hover:bg-gray-50">
                                    <td className="whitespace-nowrap px-4 py-3 font-medium">
                                        <Link href={`/sales/orders/${o.id}`} className="text-indigo-600 hover:underline">
                                            {o.order_number}
                                        </Link>
                                    </td>
                                    <td className="px-4 py-3">{o.customer?.name}</td>
                                    <td className="whitespace-nowrap px-4 py-3 text-gray-600">{dateTH(o.order_date)}</td>
                                    <td className="whitespace-nowrap px-4 py-3 text-right">{money(o.total)}</td>
                                    <td className="px-4 py-3"><StatusBadge status={o.status} /></td>
                                    <td className="whitespace-nowrap px-4 py-3 text-right">
                                        <div className="flex items-center justify-end gap-3">
                                            {NEXT_STATUS[o.status] && (
                                                <button
                                                    type="button"
                                                    onClick={() => advance(o)}
                                                    title={NEXT_ACTION[o.status]}
                                                    className="rounded border border-indigo-200 px-2 py-1 text-xs text-indigo-700 hover:bg-indigo-50"
                                                >
                                                    &rarr; {STATUS[NEXT_STATUS[o.status]].label}
                                                </button>
                                            )}
                                            <a href={`/sales/orders/${o.id}/receipt`} target="_blank" rel="noreferrer" className="text-gray-600 hover:text-gray-900">
                                                ใบเสร็จ
                                            </a>
                                            {o.status === 'pending' && (
                                                <Link href={`/sales/orders/${o.id}/edit`} className="text-gray-600 hover:text-gray-900">
                                                    แก้ไข
                                                </Link>
                                            )}
                                            <button type="button" onClick={() => remove(o)} className="text-red-600 hover:text-red-800">
                                                ลบ
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {meta && meta.last_page > 1 && (
                    <div className="flex items-center justify-between border-t border-gray-200 px-4 py-3 text-sm text-gray-600">
                        <span>
                            หน้า {meta.current_page} / {meta.last_page} (ทั้งหมด {meta.total} รายการ)
                        </span>
                        <div className="flex gap-2">
                            <button
                                type="button"
                                disabled={meta.current_page <= 1}
                                onClick={() => setPage(meta.current_page - 1)}
                                className="rounded border border-gray-300 px-3 py-1 disabled:opacity-40"
                            >
                                ก่อนหน้า
                            </button>
                            <button
                                type="button"
                                disabled={meta.current_page >= meta.last_page}
                                onClick={() => setPage(meta.current_page + 1)}
                                className="rounded border border-gray-300 px-3 py-1 disabled:opacity-40"
                            >
                                ถัดไป
                            </button>
                        </div>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}