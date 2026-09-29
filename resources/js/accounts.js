const form = document.querySelector('[data-account-form]');
if (form) {
    const checkbox = form.querySelector('[data-copy-billing]');
    const map = [
        ['billing_street', 'shipping_street'],
        ['billing_city', 'shipping_city'],
        ['billing_state', 'shipping_state'],
        ['billing_postal_code', 'shipping_postal_code'],
        ['billing_country', 'shipping_country'],
    ];

    const copyBilling = () => {
        if (!checkbox?.checked) {
            return;
        }

        map.forEach(([from, to]) => {
            const source = form.querySelector(`[name="${from}"]`);
            const target = form.querySelector(`[name="${to}"]`);
            if (source && target) {
                target.value = source.value;
            }
        });
    };

    checkbox?.addEventListener('change', copyBilling);
    map.forEach(([from]) => {
        form.querySelector(`[name="${from}"]`)?.addEventListener('input', copyBilling);
    });
}
