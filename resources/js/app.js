import './bootstrap';

const mobileMenuButton = document.querySelector('[data-mobile-menu-button]');
const mobileMenu = document.querySelector('[data-mobile-menu]');

mobileMenuButton?.addEventListener('click', () => {
    const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
    mobileMenuButton.setAttribute('aria-expanded', String(!isExpanded));
    mobileMenu?.classList.toggle('hidden');
});

document.querySelectorAll('[data-mobile-menu] a').forEach((link) => {
    link.addEventListener('click', () => {
        mobileMenu?.classList.add('hidden');
        mobileMenuButton?.setAttribute('aria-expanded', 'false');
    });
});

const bookingForm = document.querySelector('[data-booking-form]');

bookingForm?.addEventListener('submit', (event) => {
    const whatsappNumber = bookingForm.dataset.whatsappNumber;

    if (!whatsappNumber) {
        return;
    }

    event.preventDefault();

    const formData = new FormData(bookingForm);
    const car = formData.get('car_name') || 'غير محددة';
    const pickup = formData.get('pickup') || 'غير محدد';
    const destination = formData.get('destination') || 'غير محدد';
    const date = formData.get('date') || 'غير محدد';
    const time = formData.get('time') || 'غير محدد';
    const passengers = formData.get('passengers') || 'غير محدد';
    const customerName = formData.get('name') || 'غير محدد';
    const phone = formData.get('phone') || 'غير محدد';
    const normalizedNumber = whatsappNumber.replace(/\D/g, '');
    const message = [
        'مرحباً فخامة مسافر، أرغب في طلب رحلة.',
        `الاسم: ${customerName}`,
        `رقم التواصل: ${phone}`,
        `السيارة: ${car}`,
        `من: ${pickup}`,
        `إلى: ${destination}`,
        `التاريخ: ${date}`,
        `الوقت: ${time}`,
        `عدد الركاب: ${passengers}`,
    ].join('\n');

    window.open(`https://wa.me/${normalizedNumber}?text=${encodeURIComponent(message)}`, '_blank', 'noopener,noreferrer');
});
