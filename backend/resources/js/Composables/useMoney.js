/**
 * Composable for formatting currency and financial decimals
 */
export function useMoney() {
    const formatMoney = (amount, decimals = 2) => {
        const num = Number(amount || 0);
        return num.toLocaleString('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        });
    };

    const formatQty = (qty, decimals = 3) => {
        const num = Number(qty || 0);
        return num.toLocaleString('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        });
    };

    return {
        formatMoney,
        formatQty,
    };
}
