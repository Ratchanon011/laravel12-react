import axios from 'axios';

/**
 * axios instance สำหรับเรียก REST API ของ Laravel (/api/...)
 * ใช้ session cookie ของ Breeze + XSRF token (axios ใส่ให้เองสำหรับ same-origin)
 */
const api = axios.create({
    baseURL: '/api/sales',
    withCredentials: true,
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

/** ข้อความ error ที่อ่านง่ายจาก response ของ Laravel */
export function errorMessage(error) {
    const data = error?.response?.data;
    if (data?.message) return data.message;
    if (error?.response?.status === 401) return 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่';
    return 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง';
}

/** validation errors (422) ในรูปแบบ { 'items.0.product_name': 'ข้อความ' } */
export function validationErrors(error) {
    if (error?.response?.status !== 422) return {};
    const errors = error.response.data?.errors ?? {};
    return Object.fromEntries(
        Object.entries(errors).map(([key, messages]) => [key, Array.isArray(messages) ? messages[0] : messages]),
    );
}

export default api;