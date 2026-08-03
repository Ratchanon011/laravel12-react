import { Head, Link, router, useForm } from '@inertiajs/react';
import StatusPill from '@/Components/Orders/StatusPill';
import useCountdown from '@/Hooks/useCountdown';

export default function Show({ order, errors }) {
    const { post, processing } = useForm();
    const countdown = useCountdown(order.status === 'pending_payment' ? order.expires_at : null);

    const handleCancel = () => post(route('orders.cancel', order.id));
    const handleSimulatePayment = () => router.post(route('orders.simulate-payment', order.id));

    const formattedAmount = Number(order.amount).toLocaleString('th-TH', { minimumFractionDigits: 2 });

    

    return (
        <>
            <Head title={`คำสั่งซื้อ #${order.id}`} />
            <div className="flex min-h-screen items-center justify-center bg-slate-950 p-6">
                <div className="w-full max-w-sm rounded-2xl border border-slate-800 bg-slate-900 p-6 text-slate-100">
                    <div className="mb-4 font-mono text-xs text-slate-500">SHOPPING APP — ORDER #{order.id}</div>
                    <Link href={route('orders.index')} className="mb-4 inline-flex items-center gap-1 text-xs text-slate-500 hover:text-slate-300">← ดูรายการทั้งหมด</Link>
                    <div className="text-lg font-bold">คำสั่งซื้อ #{order.id}</div>
                    <div className="mt-1 text-sm text-slate-400">
                        ยอดชำระ: ฿{formattedAmount}
                        {order.status === 'processing' && <span className="text-teal-400"> (ชำระแล้ว)</span>}
                    </div>
                    <div className="mt-3"><StatusPill color={order.color} label={order.label} /></div>

                    {errors?.order && (
                        <div className="mt-4 rounded-lg border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-300">
                            {errors.order}
                        </div>
                    )}

                    {order.status === 'pending_payment' && (
                        <>
                            <div className="mt-4 rounded-xl border border-dashed border-red-500/30 bg-red-500/10 p-4 text-center">
                                <div className="text-xs text-slate-400">กรุณาชำระเงินภายใน</div>
                                <div className="mt-1 font-mono text-2xl font-semibold text-red-400">{countdown.label}</div>
                            </div>
                            <button onClick={handleSimulatePayment} className="mt-4 w-full rounded-lg bg-blue-600 py-3 text-sm font-semibold hover:bg-blue-500">
                                ชำระเงินด้วย PromptPay
                            </button>
                            <button onClick={handleCancel} disabled={!order.can_cancel || processing} className="mt-3 w-full rounded-lg border border-red-500 py-3 text-sm font-semibold text-red-400 hover:bg-red-500/10 disabled:opacity-40">
                                ยกเลิกคำสั่งซื้อ
                            </button>
                        </>
                    )}

                    {order.status === 'processing' && (
                        <>
                            <div className="mt-4 text-xs text-slate-400">ความคืบหน้าการจัดส่ง</div>
                            <div className="mt-2 h-2 overflow-hidden rounded-full bg-slate-800">
                                <div className="h-full w-2/5 rounded-full bg-teal-500" />
                            </div>
                            <div className="mt-3 flex items-start gap-2 text-sm text-slate-400">
                                <span className="text-teal-400">✔</span>ตรวจสอบยอดเงินสำเร็จ ร้านค้ากำลังแพ็คสินค้า
                            </div>
                            <button disabled className="mt-4 w-full cursor-not-allowed rounded-lg border border-slate-700 bg-slate-800 py-3 text-sm font-semibold text-slate-500">
                                ยกเลิกคำสั่งซื้อ
                            </button>
                            <div className="mt-2 text-center text-[11px] text-slate-500">*สินค้าเข้าสู่กระบวนการแพ็คแล้ว ไม่สามารถยกเลิกได้</div>
                        </>
                    )}

                    {order.status === 'cancelled' && (
                        <div className="mt-4 flex items-start gap-2 rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-300">
                            <span>✕</span>
                            <span>รายการนี้ถูกยกเลิกเนื่องจากไม่มีการชำระเงินภายใน 10 นาที ระบบได้คืนสต็อกสินค้าเรียบร้อยแล้ว</span>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}