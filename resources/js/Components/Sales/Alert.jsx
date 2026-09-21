export default function Alert({ type = 'error', children, onClose }) {
    if (!children) return null;

    const styles =
        type === 'success'
            ? 'border-green-200 bg-green-50 text-green-800'
            : 'border-red-200 bg-red-50 text-red-800';

    return (
        <div className={`mb-4 flex items-start justify-between rounded-md border px-4 py-3 text-sm ${styles}`}>
            <div>{children}</div>
            {onClose && (
                <button type="button" onClick={onClose} className="ml-4 text-lg leading-none opacity-60 hover:opacity-100">
                    &times;
                </button>
            )}
        </div>
    );
}