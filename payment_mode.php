<?php
// true: simulate payment and skip external CNPJ lookup during testing.
// false: use real CNPJ lookup. A payment gateway is not integrated yet.
define('PAYMENT_TEST_MODE', true);

// true: require email ownership verification and send a verification link.
// false: skip email verification for new registrations.
define('EMAIL_VERIFICATION_ENABLED', true);