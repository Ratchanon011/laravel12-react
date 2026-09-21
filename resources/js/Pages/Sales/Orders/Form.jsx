import { useEffect, useMemo, useRef, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Alert from '@/Components/Sales/Alert';
import api, { errorMessage, validationErrors } from '@/sales/api';
import { money, todayISO } from '@/sales/format';

const inputClass =
    'block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500';

function FieldError({ children }) {
    return children ? <p className="mt-1 text-xs text-red-600">{children}</p> : null;
}

/** ใช้ทั้งหน้า "สร้างใบสั่งซื้อ" (ไม่มี orderId) และ "แก้ไขใบสั่งซื้อ" (มี orderId) */
export default function Form({ orderId = null }) {
    const isEdit = orderId !== null;
    const keySeq = useRef(0);
    const newItem = (data = {}) => ({
        key: ++keySeq.current,
        product_id: '',
        product_name: '',
        unit_price: '',
        quantity: 1,
        ...data,
    });

    const [customers, setCustomers] = useState([]);
    const [products, setProducts] = useState([]);
    const [form, setForm] = useState({ customer_id: '', order_date: todayISO(), note: '' });
    const [items, setItems] = useState(() => [newItem()]);
    const [status, setStatus] = useState('pending');
    const [orderNumber, setOrderNumber] = useState('');
    const [errors, setErrors] = useState({});
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    const [showNewCustomer, setShowNewCustomer] = useState(false);
    const [newCustomer, setNewCustomer] = useState({ name: '', phone: '', email: '', address: '' });
    const [customerErrors, setCustomerErrors] = useState({});

    // โหลดข้อมูลลูกค้า/สินค้า (และข้อมูลออเดอร์เดิมถ้าเป็นการแก้ไข)
    useEffect(() => {
        let cancelled = false;
        (async () => {
            try {
                const requests = [api.get('/customers'), api.get('/products')];
                if (isEdit) requests.push(api.get(`/orders/${orderId}`));
                const [c, p, o] = await Promise.all(requests);
                if (cancelled) return;

                setCustomers(c.data.data);
                setProducts(p.data.data);

                if (o) {
                    const order = o.data.data;
                    setStatus(order.status);
                    setOrderNumber(order.order_number);
                    setForm({
                        customer_id: String(order.customer.id),
                        order_date: order.order_date,
                        note: order.note ?? '',
                    });
                    setItems(
                        order.items.map((i) =>
                            newItem({
                                product_id: i.product_id ? String(i.product_id) : '',
                                product_name: i.product_name,
                                unit_price: i.unit_price,
                                quantity: i.quantity,
                            }),
                        ),
                    );
                }
            } catch (e) {
                if (!cancelled) setError(errorMessage(e));
            } finally {
                if (!cancelled) setLoading(false);
            }
        })();
        return () => {
            cancelled = true;
        };
    }, [orderId]);

    const total = useMemo(
        () => items.reduce((sum, i) => sum + (Number(i.unit_price) || 0) * (Number(i.quantity) || 0), 0),
        [items],
    );

    const setField = (name, value) => setForm((f) => ({ ...f, [name]: value }));

    const updateItem = (key, patch) =>
        setItems((list) => list.map((i) => (i.key === key ? { ...i, ...patch } : i)));

    const pickProduct = (key, productId) => {
        const product = products.find((p) => String(p.id) === productId);
        if (product) {
            updateItem(key, { product_id: productId, product_name: product.name, unit_price: product.price });
        } else {
            updateItem(key, { product_id: '' });
        }
    };

    const removeItem = (key) => setItems((list) => (list.length > 1 ? list.filter((i) => i.key !== key) : list));

    const saveCustomer = async () => {
        setCustomerErrors({});
        try {
            const res = await api.post('/customers', newCustomer);
            const created = res.data.data;
            setCustomers((list) => [...list, created].sort((a, b) => a.name.localeCompare(b.name, 'th')));
            setField('customer_id', String(created.id));
            setNewCustomer({ name: '', phone: '', email: '', address: '' });
            setShowNewCustomer(false);
        } catch (e) {
            const errs = validationErrors(e);
            if (Object.keys(errs).length) setCustomerErrors(errs);
            else setError(errorMessage(e));
        }
    };

    const submit = async (e) => {
        e.preventDefault();
        setSaving(true);
        setErrors({});
        setError('');

        const payload = {
            customer_id: form.customer_id || null,
            order_date: form.order_date,
            note: form.note || null,
            items: items.map((i) => ({
                product_id: i.product_id ? Number(i.product_id) : null,
                product_name: i.product_name,
                unit_price: i.unit_price === '' ? null : Number(i.unit_price),
                quantity: i.quantity === '' ? null : Number(i.quantity),
            })),
        };

        try {
            const res = isEdit ? await api.put(`/orders/${orderId}`, payload) : await api.post('/orders', payload);
            router.visit(`/sales/orders/${res.data.data.id}`);
        } catch (err) {
            const errs = validationErrors(err);
            if (Object.keys(errs).length) {
                setErrors(errs);
                setError('กรุณาตรวจสอบข้อมูลที่กรอกอีกครั้ง');
            } else {
                setError(errorMessage(err));
            }
            setSaving(false);
        }
    };

    const locked = isEdit && status !== 'pending';

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold text-gray-800">
                    {isEdit ? `แก้ไขใบสั่งซื้อ ${orderNumber}` : 'สร้างใบสั่งซื้อ'}
                </h2>
            }
        >
            <Head title={isEdit ? 'แก้ไขใบสั่งซื้อ' : 'สร้างใบสั่งซื้อ'} />

            <Alert onClose={() => setError('')}>{error}</Alert>

            {locked && (
                <Alert>
                    ออเดอร์นี้จัดส่งแล้ว ไม่สามารถแก้ไขได้ (แก้ไขได้เฉพาะสถานะ Pending){' '}
                    <Link href={`/sales/orders/${orderId}`} className="underline">กลับไปดูรายละเอียด</Link>
                </Alert>
            )}

            {loading ? (
                <div className="rounded-lg bg-white p-10 text-center text-gray-400 shadow-sm">กำลังโหลด...</div>
            ) : (
                <form onSubmit={submit} className="space-y-6">
                    <fieldset disabled={locked || saving} className="space-y-6">
                        {/* ข้อมูลทั่วไป */}
                        <div className="rounded-lg bg-white p-6 shadow-sm">
                            <h3 className="mb-4 font-semibold text-gray-800">ข้อมูลใบสั่งซื้อ</h3>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">ลูกค้า *</label>
                                    <div className="flex gap-2">
                                        <select
                                            value={form.customer_id}
                                            onChange={(e) => setField('customer_id', e.target.value)}
                                            className={inputClass}
                                        >
                                            <option value="">-- เลือกลูกค้า --</option>
                                            {customers.map((c) => (
                                                <option key={c.id} value={c.id}>{c.name}</option>
                                            ))}
                                        </select>
                                        <button
                                            type="button"
                                            onClick={() => setShowNewCustomer((v) => !v)}
                                            className="whitespace-nowrap rounded-md border border-gray-300 px-3 text-sm text-gray-700 hover:bg-gray-50"
                                        >
                                            + ลูกค้าใหม่
                                        </button>
                                    </div>
                                    <FieldError>{errors.customer_id}</FieldError>
                                </div>
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">วันที่สั่งซื้อ *</label>
                                    <input
                                        type="date"
                                        value={form.order_date}
                                        onChange={(e) => setField('order_date', e.target.value)}
                                        className={inputClass}
                                    />
                                    <FieldError>{errors.order_date}</FieldError>
                                </div>
                                <div className="sm:col-span-2">
                                    <label className="mb-1 block text-sm font-medium text-gray-700">หมายเหตุ</label>
                                    <textarea
                                        rows={2}
                                        value={form.note}
                                        onChange={(e) => setField('note', e.target.value)}
                                        className={inputClass}
                                    />
                                    <FieldError>{errors.note}</FieldError>
                                </div>
                            </div>

                            {showNewCustomer && (
                                <div className="mt-4 rounded-md border border-indigo-100 bg-indigo-50 p-4">
                                    <div className="mb-3 text-sm font-medium text-indigo-900">เพิ่มลูกค้าใหม่</div>
                                    <div className="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <input
                                                placeholder="ชื่อลูกค้า *"
                                                value={newCustomer.name}
                                                onChange={(e) => setNewCustomer({ ...newCustomer, name: e.target.value })}
                                                className={inputClass}
                                            />
                                            <FieldError>{customerErrors.name}</FieldError>
                                        </div>
                                        <div>
                                            <input
                                                placeholder="เบอร์โทร"
                                                value={newCustomer.phone}
                                                onChange={(e) => setNewCustomer({ ...newCustomer, phone: e.target.value })}
                                                className={inputClass}
                                            />
                                            <FieldError>{customerErrors.phone}</FieldError>
                                        </div>
                                        <div>
                                            <input
                                                placeholder="อีเมล"
                                                value={newCustomer.email}
                                                onChange={(e) => setNewCustomer({ ...newCustomer, email: e.target.value })}
                                                className={inputClass}
                                            />
                                            <FieldError>{customerErrors.email}</FieldError>
                                        </div>
                                        <div>
                                            <input
                                                placeholder="ที่อยู่"
                                                value={newCustomer.address}
                                                onChange={(e) => setNewCustomer({ ...newCustomer, address: e.target.value })}
                                                className={inputClass}
                                            />
                                            <FieldError>{customerErrors.address}</FieldError>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={saveCustomer}
                                        className="mt-3 rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500"
                                    >
                                        บันทึกลูกค้า
                                    </button>
                                </div>
                            )}
                        </div>

                        {/* รายการสินค้า */}
                        <div className="rounded-lg bg-white p-6 shadow-sm">
                            <div className="mb-4 flex items-center justify-between">
                                <h3 className="font-semibold text-gray-800">รายการสินค้า</h3>
                                <button
                                    type="button"
                                    onClick={() => setItems((list) => [...list, newItem()])}
                                    className="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                                >
                                    + เพิ่มรายการ
                                </button>
                            </div>
                            <FieldError>{errors.items}</FieldError>

                            <div className="space-y-3">
                                {items.map((item, index) => (
                                    <div key={item.key} className="grid gap-2 rounded-md border border-gray-200 p-3 sm:grid-cols-12">
                                        <div className="sm:col-span-3">
                                            <label className="mb-1 block text-xs text-gray-500">เลือกจากสินค้า</label>
                                            <select
                                                value={item.product_id}
                                                onChange={(e) => pickProduct(item.key, e.target.value)}
                                                className={inputClass}
                                            >
                                                <option value="">-- กำหนดเอง --</option>
                                                {products.map((p) => (
                                                    <option key={p.id} value={p.id}>{p.name}</option>
                                                ))}
                                            </select>
                                        </div>
                                        <div className="sm:col-span-3">
                                            <label className="mb-1 block text-xs text-gray-500">ชื่อสินค้า *</label>
                                            <input
                                                value={item.product_name}
                                                onChange={(e) => updateItem(item.key, { product_name: e.target.value })}
                                                className={inputClass}
                                            />
                                            <FieldError>{errors[`items.${index}.product_name`]}</FieldError>
                                        </div>
                                        <div className="sm:col-span-2">
                                            <label className="mb-1 block text-xs text-gray-500">ราคา/หน่วย *</label>
                                            <input
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                value={item.unit_price}
                                                onChange={(e) => updateItem(item.key, { unit_price: e.target.value })}
                                                className={inputClass}
                                            />
                                            <FieldError>{errors[`items.${index}.unit_price`]}</FieldError>
                                        </div>
                                        <div className="sm:col-span-1">
                                            <label className="mb-1 block text-xs text-gray-500">จำนวน *</label>
                                            <input
                                                type="number"
                                                min="1"
                                                step="1"
                                                value={item.quantity}
                                                onChange={(e) => updateItem(item.key, { quantity: e.target.value })}
                                                className={inputClass}
                                            />
                                            <FieldError>{errors[`items.${index}.quantity`]}</FieldError>
                                        </div>
                                        <div className="flex items-end justify-between sm:col-span-3">
                                            <div>
                                                <div className="mb-1 text-xs text-gray-500">รวม</div>
                                                <div className="text-sm font-medium">
                                                    {money((Number(item.unit_price) || 0) * (Number(item.quantity) || 0))}
                                                </div>
                                            </div>
                                            <button
                                                type="button"
                                                onClick={() => removeItem(item.key)}
                                                disabled={items.length === 1}
                                                className="text-sm text-red-600 hover:text-red-800 disabled:opacity-30"
                                            >
                                                ลบ
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            <div className="mt-4 flex justify-end border-t border-gray-100 pt-4 text-lg">
                                <span className="mr-4 text-gray-600">ยอดรวม</span>
                                <span className="font-semibold text-gray-900">{money(total)}</span>
                            </div>
                        </div>
                    </fieldset>

                    <div className="flex justify-end gap-3">
                        <Link
                            href={isEdit ? `/sales/orders/${orderId}` : '/sales/orders'}
                            className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                        >
                            ยกเลิก
                        </Link>
                        <button
                            type="submit"
                            disabled={locked || saving}
                            className="rounded-md bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
                        >
                            {saving ? 'กำลังบันทึก...' : isEdit ? 'บันทึกการแก้ไข' : 'สร้างใบสั่งซื้อ'}
                        </button>
                    </div>
                </form>
            )}
        </AuthenticatedLayout>
    );
}