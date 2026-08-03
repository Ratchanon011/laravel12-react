import { useEffect, useRef, useState } from 'react';
import { router } from '@inertiajs/react';

export default function useCountdown(expiresAt) {
    const hasTriggeredReload = useRef(false);
    const [secondsLeft, setSecondsLeft] = useState(() => {
        if (!expiresAt) return 0;
        return Math.max(0, Math.floor((new Date(expiresAt) - new Date()) / 1000));
    });

    useEffect(() => {
        if (!expiresAt) return;
        hasTriggeredReload.current = false;

        const tick = () => {
            const remaining = Math.max(0, Math.floor((new Date(expiresAt) - new Date()) / 1000));
            setSecondsLeft(remaining);

            if (remaining <= 0 && !hasTriggeredReload.current) {
                hasTriggeredReload.current = true;
                router.reload({ only: ['order'] });
            }
        };

        tick();
        const interval = setInterval(tick, 1000);
        return () => clearInterval(interval);
    }, [expiresAt]);

    const minutes = String(Math.floor(secondsLeft / 60)).padStart(2, '0');
    const seconds = String(secondsLeft % 60).padStart(2, '0');

    return { secondsLeft, label: `${minutes}:${seconds}` };
}