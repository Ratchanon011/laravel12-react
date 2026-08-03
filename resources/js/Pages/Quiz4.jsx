import React, { useState, useEffect } from 'react';

export default function Quiz4() {
    const [employees, setEmployees] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        // ใช้ Fetch API ดึงข้อมูลจาก API Route ที่เราสร้างไว้ (ตามโจทย์ Optional)
        fetch('/api/quiz4-data')
            .then(response => response.json())
            .then(data => {
                setEmployees(data);
                setLoading(false);
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                setLoading(false);
            });
    }, []);

    return (
        <div style={{ padding: '30px', fontFamily: 'sans-serif', maxWidth: '800px', margin: '0 auto' }}>
            <h1 style={{ color: '#0284c7', borderBottom: '2px solid #e2e8f0', paddingBottom: '10px' }}>
                ระบบจัดการข้อมูลพนักงาน (Quiz 4)
            </h1>
            
            {loading ? (
                <p>กำลังโหลดข้อมูล...</p>
            ) : (
                <table style={{ width: '100%', borderCollapse: 'collapse', marginTop: '20px', boxShadow: '0 4px 6px -1px rgba(0,0,0,0.1)' }}>
                    <thead style={{ backgroundColor: '#f1f5f9', textAlign: 'left' }}>
                        <tr>
                            <th style={{ padding: '12px', borderBottom: '1px solid #cbd5e1' }}>รหัสพนักงาน</th>
                            <th style={{ padding: '12px', borderBottom: '1px solid #cbd5e1' }}>ชื่อ</th>
                            <th style={{ padding: '12px', borderBottom: '1px solid #cbd5e1' }}>นามสกุล</th>
                            <th style={{ padding: '12px', borderBottom: '1px solid #cbd5e1' }}>แผนก</th>
                            <th style={{ padding: '12px', borderBottom: '1px solid #cbd5e1' }}>เงินเดือน (บาท)</th>
                        </tr>
                    </thead>
                    <tbody>
                        {employees.map((emp) => (
                            <tr key={emp.id} style={{ borderBottom: '1px solid #e2e8f0' }}>
                                <td style={{ padding: '12px' }}>{emp.emp_code}</td>
                                <td style={{ padding: '12px' }}>{emp.first_name}</td>
                                <td style={{ padding: '12px' }}>{emp.last_name}</td>
                                <td style={{ padding: '12px' }}>{emp.department}</td>
                                <td style={{ padding: '12px' }}>{parseFloat(emp.salary).toLocaleString()}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}
        </div>
    );
}