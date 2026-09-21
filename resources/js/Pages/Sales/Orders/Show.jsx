import { useCallback, useEffect, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Alert from '@/Components/Sales/Alert';
import StatusBadge from '@/Components/Sales/StatusBadge';
import api, { errorMessage } from '@/sales/api';
import { STATUS, STATUS_ORDER, NEXT_STATUS, NEXT_ACTION, money, dateTH, dateTimeTH } from '@/sales/format';

export default function Show({ orderId }) {
    const [order, setOrder] = useState(null);
    const [error, setError] = useState('');
    const [notice, setNotice] = useState('');
    const [busy, setBusy] = useState(false);

    const load = useCallback(async () => {
        try {
            const res = await api.get(`/orders/${orderId}`);
            setOrder(res.data.data);
        } catch (e) {
            setError(errorMessage(e));
        }
    }, [orderId]);

    useEffect(() => {
        load();
    }, [load]);

    const advance = async () => {
        const next = NEXT_STATUS[order.status];
        if (!next) return;
        setBusy(true);
        try {
            const res = await api.patch(`/orders/${orderId}/status`, { status: next });
            setOrder(res.data.data);
            setNotice(`เปลี่ยนสถานะเป็น ${STATUS[next].label} เรียบร้อยแล้ว`);
            setError('');
        } catch (e) {
            setError(errorMessage(e));
        } finally {
            setBusy(false);
        }
    };

    const remove = async () => {
        if (!window.confirm(`ต้องการลบใบสั่งซื้อ ${order.order_number} ใช่หรือไม่?`)) return;
        try {
            await api.delete(`/orders/${orderId}`);
            router.visit('/sales/orders');
        } catch (e) {
            setError(errorMessage(e));
        }
    };

    const historyAt = (status) => order?.histories?.find((h) => h.status === status)?.at;
    const currentIndex = order ? STATUS_ORDER.indexOf(order.status) : 0;

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div className="flex items-center gap-3">
                        <h2 className="text-xl font-semibold text-gray-800">
                            ใบสั่งซื้อ {order ? order.order_number : ''}
                        </h2>
                        {order && <StatusBadge status={order.status} />}
                    </div>
                    <Link href="/sales/orders" className="text-sm text-gray-600 hover:text-gray-900">&larr; กลับไปรายการ</Link>
                </div>
            }
        >
            <Head title={order ? order.order_number : 'ใบสั่งซื้อ'} />

            <Alert type="success" onClose={() => setNotice('')}>{notice}</Alert>
            <Alert onClose={() => setError('')}>{error}</Alert>

            {!order ? (
                <div className="rounded-lg bg-white p-10 text-center text-gray-400 shadow-sm">
                    {error ? 'ไม่สามารถโหลดข้อมูลได้' : 'กำลังโหลด...'}
                </div>
            ) : (
                <div className="space-y-6">
                    {/* ปุ่มดำเนินการ */}
                    <div className="flex flex-wrap items-center gap-3 rounded-lg bg-white p-4 shadow-sm">
                        {NEXT_STATUS[order.status] && (
                            <button
                                type="button"
                                onClick={advance}
                                disabled={busy}
                                className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
                            >
                                {NEXT_ACTION[order.status]} &rarr; {STATUS[NEXT_STATUS[order.status]].label}
                            </button>
                        )}
                        <a
                            href={`/sales/orders/${order.id}/receipt`}
                            target="_blank"
                            rel="noreferrer"
                            className="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                        >
                            ดูใบเสร็จ (PDF)
                        </a>
                        <a
                            href={`/sales/orders/${order.id}/receipt?download=1`}
                            className="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                        >
                            ดาวน์โหลด PDF
                        </a>
                        {order.status === 'pending' && (
                            <Link
                                href={`/sales/orders/${order.id}/edit`}
                                className="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                            >
                                แก้ไข
                            </Link>
                        )}
                        <button type="button" onClick={remove} className="ml-auto text-sm text-red-600 hover:text-red-800">
                            ลบใบสั่งซื้อ
                        </button>
                    </div>

                    {/* ติดตามสถานะ */}
                    <div className="rounded-lg bg-white p-6 shadow-sm">
                        <h3 className="mb-5 font-semibold text-gray-800">ติดตามสถานะ</h3>
                        <ol className="grid gap-4 sm:grid-cols-3">
                            {STATUS_ORDER.map((key, i) => {
                                const done = i <= currentIndex;
                                return (
                                    <li key={key} className="flex items-start gap-3">
                                        <span
                                            className={`mt-0.5 flex h-7 w-7 flex-none items-center justify-center rounded-full text-sm font-semibold ${
                                                done ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500'
                                            }`}
                                        >
                                            {done ? '✓' : i + 1}
                                        </span>
                                        <div>
                                            <div className={`text-sm font-medium ${done ? 'text-gray-900' : 'text-gray-400'}`}>
                                                {STATUS[key].label}
                                            </div>
                                            <div className="text-xs text-gray-500">{STATUS[key].th}</div>
                                            <div className="text-xs text-gray-400">{done ? dateTimeTH(historyAt(key)) : ''}</div>
                                        </div>
                                    </li>
                                );
                            })}
                        </ol>
                    </div>

                    <div className="grid gap-6 lg:grid-cols-3">
                        {/* ข้อมูลลูกค้า */}
                        <div className="rounded-lg bg-white p-6 shadow-sm">
                            <h3 className="mb-3 font-semibold text-gray-800">ลูกค้า</h3>
                            <div className="space-y-1 text-sm text-gray-700">
                                <div className="font-medium">{order.customer.name}</div>
                                {order.customer.phone && <div>โทร: {order.customer.phone}</div>}
                                {order.customer.email && <div>{order.customer.email}</div>}
                                {order.customer.address && <div className="text-gray-500">{order.customer.address}</div>}
                            </div>
                            <div className="mt-4 border-t border-gray-100 pt-3 text-sm text-gray-600">
                                <div>วันที่สั่งซื้อ: {dateTH(order.order_date)}</div>
                                {order.note && <div className="mt-1">หมายเหตุ: {order.note}</div>}
                            </div>
                        </div>

                        {/* รายการสินค้า */}
                        <div className="rounded-lg bg-white p-6 shadow-sm lg:col-span-2">
                            <h3 className="mb-3 font-semibold text-gray-800">รายการสินค้า</h3>
                            <div className="overflow-x-auto">
                                <table className="min-w-full text-sm">
                                    <thead className="border-b border-gray-200 text-left text-xs uppercase text-gray-500">
                                        <tr>
                                            <th className="py-2 pr-3">สินค้า</th>
                                            <th className="px-3 py-2 text-right">ราคา/หน่วย</th>
                                            <th className="px-3 py-2 text-right">จำนวน</th>
                                            <th className="py-2 pl-3 text-right">รวม</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100">
                                        {order.items.map((item) => (
                                            <tr key={item.id}>
                                                <td className="py-2 pr-3">{item.product_name}</td>
                                                <td className="px-3 py-2 text-right">{money(item.unit_price)}</td>
                                                <td className="px-3 py-2 text-right">{item.quantity}</td>
                                                <td className="py-2 pl-3 text-right">{money(item.line_total)}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                    <tfoot>
                                        <tr className="border-t border-gray-200">
                                            <td colSpan={3} className="py-3 pr-3 text-right font-medium text-gray-700">ยอดรวมสุทธิ</td>
                                            <td className="py-3 pl-3 text-right text-base font-semibold text-gray-900">{money(order.total)}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}