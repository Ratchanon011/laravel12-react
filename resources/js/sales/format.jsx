// หมายเหตุ: ไฟล์นี้ใช้นามสกุล .jsx เพื่อให้ Tailwind (v3) สแกน class ด้านล่างเจอ

export const money = (value) =>
    new Intl.NumberFormat('th-TH', { style: 'currency', currency: 'THB' }).format(Number(value ?? 0));

export const dateTH = (value) => {
    if (!value) return '-';
    // 'YYYY-MM-DD' -> ใส่เวลากลางวันเพื่อกัน timezone ทำให้วันเลื่อน
    const d = new Date(/^\d{4}-\d{2}-\d{2}$/.test(value) ? `${value}T12:00:00` : value);
    return d.toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' });
};

export const dateTimeTH = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleString('th-TH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

export const todayISO = () => {
    const d = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
};

export const STATUS = {
    pending: {
        label: 'Pending',
        th: 'รอดำเนินการ',
        badge: 'bg-yellow-100 text-yellow-800 ring-yellow-600/20',
        dot: 'bg-yellow-500',
        tab: 'border-yellow-500 text-yellow-700',
    },
    shipped: {
        label: 'Shipped',
        th: 'กำลังจัดส่ง',
        badge: 'bg-blue-100 text-blue-800 ring-blue-600/20',
        dot: 'bg-blue-500',
        tab: 'border-blue-500 text-blue-700',
    },
    delivered: {
        label: 'Delivered',
        th: 'จัดส่งสำเร็จ',
        badge: 'bg-green-100 text-green-800 ring-green-600/20',
        dot: 'bg-green-500',
        tab: 'border-green-500 text-green-700',
    },
};

export const STATUS_ORDER = ['pending', 'shipped', 'delivered'];

/** สถานะถัดไป (Pending -> Shipped -> Delivered) */
export const NEXT_STATUS = { pending: 'shipped', shipped: 'delivered', delivered: null };

/** ข้อความบนปุ่มเปลี่ยนสถานะ */
export const NEXT_ACTION = { pending: 'ทำเครื่องหมายว่าจัดส่งแล้ว', shipped: 'ทำเครื่องหมายว่าส่งมอบแล้ว' };