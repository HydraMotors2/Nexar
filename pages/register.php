<?php
require_once __DIR__ . '/../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

require_once __DIR__ . '/../php/database.php';

function safe(string $value = ''): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function generateUuid(): string {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// Initialize registration session
if (!isset($_SESSION['registration'])) {
    $_SESSION['registration'] = ['account_type' => null, 'form_data' => [], 'step' => 1];
}

$accountType = $_SESSION['registration']['account_type'] ?? null;
$registerError = '';
$errors = [];
$step = $_SESSION['registration']['step'] ?? 1;
$formData = $_SESSION['registration']['form_data'] ?? [];
$hasFormData = !empty($formData);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;

    if ($action === 'select_type') {
        $selectedType = $_POST['account_type'] ?? '';
        if (in_array($selectedType, ['entrepreneur', 'supplier'], true)) {
            $_SESSION['registration']['account_type'] = $selectedType;
            $_SESSION['registration']['step'] = 1;
            header('Location: /NEXAR/register');
            exit;
        }

        $registerError = 'Por favor selecione o tipo de conta correto.';
    }

    if ($action === 'change_account_type') {
        unset($_SESSION['registration']);
        header('Location: /NEXAR/register');
        exit;
    }

    if ($action === 'update_step') {
        $accountType = $_SESSION['registration']['account_type'] ?? null;
        $newStep = (int)($_POST['step'] ?? 1);
        $maxStep = $accountType === 'supplier' ? 4 : 2;

        if ($newStep >= 1 && $newStep <= $maxStep) {
            $_SESSION['registration']['step'] = $newStep;
            // Store form data temporarily for preservation
            $formKeys = ['fullName', 'email', 'password', 'passwordConfirm', 'cnpj', 'companyLegalName', 
                         'phone', 'segment', 'city', 'state', 'numberEmployees', 'revenueRange',
                         'interestedCategories', 'productsPurchased', 'purchaseFrequency', 'companyDescription',
                         'category', 'mainProducts', 'serviceRegion', 'website', 'whatsapp', 'plan', 'termsAccepted'];
            foreach ($formKeys as $key) {
                if (isset($_POST[$key])) {
                    $_SESSION['registration']['form_data'][$key] = $_POST[$key];
                }
            }
            header('Location: /NEXAR/register');
            exit;
        }
    }

    if ($action === 'complete_registration') {
        $accountType = $_SESSION['registration']['account_type'] ?? ($_POST['account_type'] ?? null);
        $step = max(1, min((int)($_POST['current_step'] ?? 1), $accountType === 'supplier' ? 4 : 2));

        if (!in_array($accountType, ['entrepreneur', 'supplier'], true)) {
            $registerError = 'Tipo de conta inválido.';
        }

        $data = array_map('trim', $_POST);
        $data += [
            'fullName' => '',
            'email' => '',
            'password' => '',
            'passwordConfirm' => '',
            'cnpj' => '',
            'companyLegalName' => '',
            'phone' => '',
            'segment' => '',
            'city' => '',
            'state' => '',
            'numberEmployees' => '',
            'revenueRange' => '',
            'interestedCategories' => '',
            'productsPurchased' => '',
            'purchaseFrequency' => '',
            'companyDescription' => '',
            'category' => '',
            'mainProducts' => '',
            'serviceRegion' => '',
            'website' => '',
            'whatsapp' => '',
            'plan' => '',
        ];

        if ($data['fullName'] === '') {
            $errors['fullName'] = 'Nome completo é obrigatório.';
        }

        if ($data['email'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'E-mail válido é obrigatório.';
        }

        if ($data['password'] === '' || strlen($data['password']) < 8) {
            $errors['password'] = 'Senha deve ter ao menos 8 caracteres.';
        }

        if ($data['password'] !== $data['passwordConfirm']) {
            $errors['passwordConfirm'] = 'As senhas não coincidem.';
        }

        if ($data['phone'] === '') {
            $errors['phone'] = 'Telefone é obrigatório.';
        }

        if ($data['cnpj'] === '') {
            $errors['cnpj'] = 'CNPJ é obrigatório.';
        } else {
            $cnpjLookup = lookupCnpj($data['cnpj']);
            if (!$cnpjLookup['valid']) {
                $errors['cnpj'] = $cnpjLookup['error'];
            } elseif (!$cnpjLookup['exists']) {
                $errors['cnpj'] = $cnpjLookup['error'] ?? 'CNPJ não encontrado na BrasilAPI.';
            } else {
                $data['cnpj'] = $cnpjLookup['digits'];
            }
        }

        if ($accountType === 'entrepreneur') {
            $required = ['companyLegalName', 'numberEmployees', 'revenueRange', 'purchaseFrequency'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = 'Campo obrigatório.';
                }
            }

            if (empty($_POST['termsAccepted'])) {
                $errors['termsAccepted'] = 'É obrigatório aceitar os termos para concluir o cadastro.';
            }
        }

        if ($accountType === 'supplier') {
            $required = ['companyLegalName', 'category', 'companyDescription', 'plan'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = 'Campo obrigatório.';
                }
            }
        }

        if (empty($errors)) {
            $db = Database::getInstance();

            $existingEmail = $db->count('users', 'email = :email', ['email' => $data['email']]);
            if ($existingEmail > 0) {
                $errors['email'] = 'E-mail já está em uso.';
            }

            if (!empty($data['cnpj'])) {
                $existingCnpj = $db->count('entrepreneurs', 'cnpj = :cnpj', ['cnpj' => $data['cnpj']]);
                $existingCnpj += $db->count('suppliers', 'cnpj = :cnpj', ['cnpj' => $data['cnpj']]);
                if ($existingCnpj > 0) {
                    $errors['cnpj'] = 'CNPJ já registrado.';
                }
            }
        }

        if (empty($errors)) {
            $db = Database::getInstance();
            $nameParts = preg_split('/\s+/', $data['fullName']);
            $firstName = $nameParts[0] ?? '';
            $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : $firstName;
            $userId = null;

            try {
                $userId = $db->insert('users', [
                    'uuid' => generateUuid(),
                    'email' => $data['email'],
                    'password' => password_hash($data['password'], PASSWORD_BCRYPT),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'account_type' => $accountType,
                    'role' => $accountType === 'supplier' ? 'provider' : 'client',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (Exception $e) {
                $registerError = 'Falha ao criar conta. Tente novamente.';
            }

            if ($userId && $accountType === 'entrepreneur') {
                $photoData = null;
                /*
                if (isset($_FILES['companyPhoto']) && $_FILES['companyPhoto']['size'] > 0) {
                    $photoData = file_get_contents($_FILES['companyPhoto']['tmp_name']);
                }
                */

                $db->insert('entrepreneurs', [
                    'user_id' => $userId,
                    'cnpj' => $data['cnpj'],
                    'legal_name' => $data['companyLegalName'],
                    'trade_name' => $data['tradeName'] ?? $data['companyLegalName'],
                    'phone' => $data['phone'] ?: null,
                    'business_segment' => $data['segment'] ?: null,
                    'city' => $data['city'] ?: null,
                    'state' => $data['state'] ?: null,
                    'company_photo' => $photoData,
                    'employees_range' => $data['numberEmployees'] ?: null,
                    'revenue_range' => $data['revenueRange'] ?: null,
                    'interested_categories' => $data['interestedCategories'] ?: null,
                    'products_purchased' => $data['productsPurchased'] ?: null,
                    'purchase_frequency' => $data['purchaseFrequency'] ?: null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            if ($userId && $accountType === 'supplier') {
                $logoData = null;
                $coverData = null;
                /*
                if (isset($_FILES['companyLogo']) && $_FILES['companyLogo']['size'] > 0) {
                    $logoData = file_get_contents($_FILES['companyLogo']['tmp_name']);
                }
                if (isset($_FILES['coverImage']) && $_FILES['coverImage']['size'] > 0) {
                    $coverData = file_get_contents($_FILES['coverImage']['tmp_name']);
                }
                */

                $db->insert('suppliers', [
                    'user_id' => $userId,
                    'cnpj' => $data['cnpj'],
                    'legal_name' => $data['companyLegalName'],
                    'trade_name' => $data['tradeName'] ?? $data['companyLegalName'],
                    'phone' => $data['phone'] ?: null,
                    'city' => $data['city'] ?: null,
                    'state' => $data['state'] ?: null,
                    'business_segment' => $data['segment'] ?: null,
                    'logo' => $logoData,
                    'cover_image' => $coverData,
                    'description' => $data['companyDescription'] ?: null,
                    'category' => $data['category'] ?: null,
                    'main_products' => $data['mainProducts'] ?: null,
                    'service_region' => $data['serviceRegion'] ?: null,
                    'website' => $data['website'] ?: null,
                    'whatsapp' => $data['whatsapp'] ?: null,
                    'plan' => $data['plan'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            if ($userId) {
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $data['fullName'];
                $_SESSION['user_email'] = $data['email'];
                $_SESSION['user_role'] = $accountType === 'supplier' ? 'provider' : 'client';
                $_SESSION['account_type'] = $accountType;
                $_SESSION['logged_in'] = true;
                unset($_SESSION['registration']);

                header('Location: /NEXAR/');
                exit;
            }

            if (empty($registerError)) {
                $registerError = 'Não foi possível concluir o cadastro. Por favor, tente novamente.';
            }
        } else {
            // Store form data for preservation
            foreach ($data as $key => $value) {
                $_SESSION['registration']['form_data'][$key] = $value;
            }
        }
    }
}

$step = $_SESSION['registration']['step'] ?? 1;
$values = array_map('safe', $formData + $_POST);
if (isset($values['cnpj'])) {
    $values['cnpj'] = safe(normalizeCnpj((string)$values['cnpj']));
}
?>

<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include __DIR__ . '/../components/favicon.php'; ?>
    <title>Cadastro - NEXAR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/NEXAR/public/css/variables.css">
    <link rel="stylesheet" href="/NEXAR/public/css/global.css">
    <link rel="stylesheet" href="/NEXAR/public/css/components.css">
    <link rel="stylesheet" href="/NEXAR/public/css/animations.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 32px 16px 48px;
            overflow-x: hidden;
        }
        .register-wrapper {
            width: 100%;
            max-width: 620px;
            margin: 0 auto;
            padding: 0 16px;
            box-sizing: border-box;
        }
        .register-wrapper *,
        .register-wrapper *::before,
        .register-wrapper *::after {
            box-sizing: border-box;
        }
        .register-branding {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 8px;
            margin-bottom: 18px;
        }
        .brand-title {
            font-size: 28px;
            font-weight: 800;
            color: #ff6b35;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            margin: 0;
            overflow-wrap: anywhere;
        }
        .brand-subtitle {
            font-size: 14px;
            color: #c7c7c7;
            max-width: 620px;
            line-height: 1.7;
            margin: 0;
            overflow-wrap: anywhere;
        }
        .register-card {
            background: rgba(10, 10, 12, 0.96);
            backdrop-filter: blur(22px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 28px 70px rgba(0, 0, 0, 0.35);
            overflow: visible;
            position: relative;
            z-index: 1;
        }
        .register-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 22px;
        }
        .register-header-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
        }
        .register-title {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            margin: 0;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }
        .change-account-type-link {
            font-size: 13px;
            color: #00d4aa;
            text-decoration: none;
            font-weight: 600;
            transition: color .2s ease;
        }
        .change-account-type-link:hover {
            color: #00f0c2;
        }
        .register-form {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .account-type-grid,
        .form-row,
        .form-actions {
            display: grid;
            gap: 18px;
        }
        .account-type-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .account-type-card {
            padding: 30px;
            border-radius: 22px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.03);
            cursor: pointer;
            transition: all .2s ease;
        }
        .account-type-card:hover,
        .account-type-card.selected {
            border-color: rgba(255, 107, 53, 0.7);
            background: rgba(255, 107, 53, 0.1);
            transform: translateY(-2px);
        }
        .account-type-card h2 {
            margin: 0 0 12px;
            color: #fff;
            font-size: 1.75rem;
            line-height: 1.1;
            font-weight: 800;
        }
        .account-type-card p {
            margin: 0;
            color: #c7c7c7;
            line-height: 1.7;
        }
        .wizard-tabs {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }
        .wizard-step {
            padding: 14px 16px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.04);
            color: #b8b8b8;
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.3;
            overflow-wrap: anywhere;
        }
        .wizard-step.active {
            background: #ff6b35;
            color: #fff;
        }
        .step-section {
            padding-top: 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .section-header {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 22px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }
        .section-description {
            color: #c7c7c7;
            font-size: 13px;
            line-height: 1.7;
            margin: 0;
            overflow-wrap: anywhere;
        }
        .form-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .form-row.full {
            grid-template-columns: 1fr;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .form-label {
            color: #f1f1f1;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }
        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
            font-size: 14px;
            font-family: inherit;
            line-height: 1.4;
            min-width: 0;
            overflow-wrap: anywhere;
            transition: border .2s ease, box-shadow .2s ease;
        }
        .form-textarea {
            min-height: 130px;
            resize: vertical;
        }
        .cnpj-counter,
        .cnpj-debug-log {
            font-size: 12px;
            line-height: 1.4;
        }
        .cnpj-counter {
            color: rgba(255, 255, 255, 0.58);
            text-align: right;
            margin-top: -4px;
        }
        .cnpj-debug-log {
            color: #ffb4ab;
            min-height: 17px;
            margin-top: -6px;
        }
        .cnpj-debug-log.warning {
            color: #ffd166;
        }
        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #00d4aa;
            box-shadow: 0 0 0 3px rgba(0, 212, 170, 0.18);
            background: rgba(255, 255, 255, 0.08);
        }
        .form-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: rgba(255, 255, 255, 0.06);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23ffffff' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.16);
            padding-right: 44px;
        }
        .form-select:focus {
            z-index: 2;
            background: rgba(255, 255, 255, 0.08);
        }
        .form-select option {
            background: #111;
            color: #fff;
        }
        .option {
            background: #111;
            color: #fff;
        }
        .upload-area {
            border: 2px dashed rgba(0, 212, 170, 0.7);
            border-radius: 18px;
            padding: 36px 22px;
            text-align: center;
            background: rgba(0, 212, 170, 0.08);
            transition: background .2s ease, transform .2s ease;
            cursor: pointer;
        }
        .upload-area:hover {
            background: rgba(0, 212, 170, 0.14);
            transform: translateY(-1px);
        }
        .upload-icon {
            font-size: 30px;
            margin-bottom: 10px;
        }
        .upload-text {
            color: #d1d1d1;
            font-size: 14px;
            line-height: 1.7;
        }
        .form-actions {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-top: 18px;
        }
        .form-actions.form-actions-center {
            grid-template-columns: 1fr;
            justify-items: center;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 18px;
            min-height: 48px;
            min-width: 0;
            border-radius: 16px;
            border: none;
            font-weight: 700;
            line-height: 1.35;
            text-align: center;
            overflow-wrap: anywhere;
            cursor: pointer;
            transition: transform .2s ease, background .2s ease;
        }
        .btn-back {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
        .btn-back:hover:not(:disabled),
        .btn-submit:hover:not(:disabled) {
            transform: translateY(-1px);
        }
        .btn-submit {
            background: #ff6b35;
            color: #fff;
        }
        .btn-submit:disabled {
            background: #7d7d7d;
            cursor: not-allowed;
            opacity: 0.6;
            transform: none;
        }
        .btn-submit:disabled:hover {
            transform: none;
        }
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            color: #c7c7c7;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }
        .checkbox-wrapper input {
            accent-color: #ff6b35;
        }
        .alert {
            padding: 18px 20px;
            border-radius: 18px;
            background: rgba(255, 107, 53, 0.13);
            border: 1px solid rgba(255, 107, 53, 0.22);
            color: #ffd5c2;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }
        .field-error {
            color: #ffb8a3;
            font-size: 13px;
            min-height: 18px;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }
        /* Confirmation Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.show {
            display: flex;
        }
        .modal-content {
            background: rgba(15, 15, 15, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 32px;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .modal-title {
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            margin: 0 0 12px 0;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }
        .modal-text {
            color: #d1d1d1;
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 24px;
            overflow-wrap: anywhere;
        }
        .modal-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
        .modal-btn {
            padding: 12px 18px;
            border-radius: 14px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            transition: all .2s ease;
        }
        .modal-btn-cancel {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
        .modal-btn-cancel:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .modal-btn-confirm {
            background: #ff6b35;
            color: #fff;
        }
        .modal-btn-confirm:hover {
            background: #ff7d4d;
        }
        @media (max-width: 760px) {
            .account-type-grid,
            .form-row,
            .form-actions,
            .wizard-tabs {
                grid-template-columns: 1fr;
            }
            .register-card {
                padding: 24px;
            }
            .register-header {
                flex-direction: column;
                gap: 12px;
            }
            .register-header > div,
            .register-header-actions,
            .register-header-actions .btn-action {
                width: 100%;
            }
            .register-header-actions .btn-action {
                white-space: normal;
            }
            .account-type-card {
                padding: 24px;
            }
            .account-type-card h2 {
                font-size: 1.45rem;
                line-height: 1.25;
            }
            .wizard-step {
                border-radius: 16px;
            }
            .modal-actions {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 420px) {
            body {
                padding: 20px 8px 32px;
            }
            .register-wrapper {
                padding: 0 8px;
            }
            .register-card {
                padding: 18px;
                border-radius: 18px;
            }
            .brand-title {
                font-size: 24px;
            }
            .register-title {
                font-size: 21px;
            }
            .btn-action,
            .modal-btn {
                padding-left: 14px;
                padding-right: 14px;
            }
        }
    </style>
</head>
<body class="bg-matte">
    <div class="bg-animation" aria-hidden="true">
        <div class="bg-gradient-orb orb-1"></div>
        <div class="bg-gradient-orb orb-2"></div>
    </div>

    <div class="register-wrapper">
        <div class="register-branding">
            <h1 class="brand-title">NEXAR</h1>
            <p class="brand-subtitle">Conectando micro e pequenas empresas a fornecedores confiáveis em um marketplace B2B moderno.</p>
        </div>
        <div class="register-card">
            <div class="register-header">
                <div>
                    <h1 class="register-title">Cadastro NEXAR</h1>
                    <?php if ($accountType): ?>
                        
                    <?php endif; ?>
                </div>
                <div class="register-header-actions">
                    <a href="/NEXAR/" class="btn-action btn-back">Voltar para Início</a>
                </div>
            </div>

            <?php if (!empty($registerError)): ?>
                <div class="alert"><?php echo safe($registerError); ?></div>
            <?php endif; ?>

            <form id="registerForm" class="register-form" method="POST" enctype="multipart/form-data" novalidate>
                <?php if (!$accountType): ?>
                    <input type="hidden" name="action" value="select_type">
                    <div class="account-type-grid">
                        <label class="account-type-card" id="cardEntrepreneur">
                            <input type="radio" name="account_type" value="entrepreneur" style="display:none;">
                            <h2>Micro / Pequena Empresa</h2>
                            <p>Estou procurando fornecedores para o meu negócio.</p>
                        </label>
                        <label class="account-type-card" id="cardSupplier">
                            <input type="radio" name="account_type" value="supplier" style="display:none;">
                            <h2>Fornecedor</h2>
                            <p>Quero vender produtos ou serviços para outras empresas.</p>
                        </label>
                    </div>
                    <div class="form-actions form-actions-center">
                        <button type="submit" class="btn-action btn-submit">Continuar</button>
                    </div>
                <?php else: ?>
                    <div class="wizard-tabs">
                        <?php if ($accountType === 'entrepreneur'): ?>
                            <div class="wizard-step active" data-step="1">1. Informações</div>
                            <div class="wizard-step" data-step="2">2. Perfil</div>
                        <?php else: ?>
                            <div class="wizard-step active" data-step="1">1. Conta</div>
                            <div class="wizard-step" data-step="2">2. Empresa</div>
                            <div class="wizard-step" data-step="3">3. Perfil</div>
                            <div class="wizard-step" data-step="4">4. Plano</div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="action" value="complete_registration">
                    <input type="hidden" name="account_type" value="<?php echo safe($accountType); ?>">
                    <input type="hidden" name="current_step" id="currentStep" value="<?php echo $step; ?>">

                    <div class="step-section" data-step="1">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nome Completo <span class="required">*</span></label>
                                <input type="text" name="fullName" class="form-input" placeholder="Seu nome" value="<?php echo $values['fullName'] ?? ''; ?>">
                                <div class="field-error"><?php echo $errors['fullName'] ?? ''; ?></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">CNPJ <span class="required">*</span></label>
                                <input type="text" name="cnpj" class="form-input cnpj-input" placeholder="00.000.000/0000-00" inputmode="numeric" maxlength="18" value="<?php echo $values['cnpj'] ?? ''; ?>" required>
                                <div class="cnpj-counter" data-cnpj-counter>0/14</div>
                                <div class="cnpj-debug-log" data-cnpj-debug role="status" aria-live="polite"><?php echo safe($errors['cnpj'] ?? ''); ?></div>
                                <div class="field-error"><?php echo $errors['cnpj'] ?? ''; ?></div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Email <span class="required">*</span></label>
                                <input type="email" name="email" class="form-input" placeholder="email@empresa.com" value="<?php echo $values['email'] ?? ''; ?>" required>
                                <div class="field-error"><?php echo $errors['email'] ?? ''; ?></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Telefone <span class="required">*</span></label>
                                <input type="tel" name="phone" class="form-input" placeholder="(00) 00000-0000" value="<?php echo $values['phone'] ?? ''; ?>" required>
                                <div class="field-error"><?php echo $errors['phone'] ?? ''; ?></div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Senha <span class="required">*</span></label>
                                <input type="password" name="password" class="form-input" placeholder="Criar uma senha segura">
                                <div class="field-error"><?php echo $errors['password'] ?? ''; ?></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Confirmar Senha <span class="required">*</span></label>
                                <input type="password" name="passwordConfirm" class="form-input" placeholder="Confirmar a senha">
                                <div class="field-error"><?php echo $errors['passwordConfirm'] ?? ''; ?></div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn-action btn-back" onclick="confirmChangeAccountType(event, true);">← Voltar</button>
                            <button type="button" class="btn-action btn-submit" onclick="nextStep(2);">Próximo →</button>
                        </div>
                    </div>

                    <?php if ($accountType === 'entrepreneur'): ?>
                        <div class="step-section" data-step="2" style="display:none;">
                            <div class="section-header">
                                <h2 class="section-title">Complete o perfil da sua empresa</h2>
                                <p class="section-description">Preencha informações chave para direcionar as melhores oportunidades e destacar seu negócio no marketplace.</p>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">CNPJ <span class="required">*</span></label>
                                    <input type="text" name="cnpj" class="form-input cnpj-input" placeholder="00.000.000/0000-00" inputmode="numeric" maxlength="18" value="<?php echo $values['cnpj'] ?? ''; ?>" required>
                                    <div class="cnpj-counter" data-cnpj-counter>0/14</div>
                                    <div class="cnpj-debug-log" data-cnpj-debug role="status" aria-live="polite"><?php echo safe($errors['cnpj'] ?? ''); ?></div>
                                    <div class="field-error"><?php echo $errors['cnpj'] ?? ''; ?></div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Razão Social <span class="required">*</span></label>
                                    <input type="text" name="companyLegalName" class="form-input" placeholder="Razão social" value="<?php echo $values['companyLegalName'] ?? ''; ?>" required>
                                    <div class="field-error"><?php echo $errors['companyLegalName'] ?? ''; ?></div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Telefone <span class="required">*</span></label>
                                    <input type="tel" name="phone" class="form-input" placeholder="(00) 00000-0000" value="<?php echo $values['phone'] ?? ''; ?>" required>
                                    <div class="field-error"><?php echo $errors['phone'] ?? ''; ?></div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Segmento de Atuação</label>
                                    <input type="text" name="segment" class="form-input" placeholder="Ex: Alimentação, Moda..." value="<?php echo $values['segment'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Cidade</label>
                                    <input type="text" name="city" class="form-input" placeholder="Sua cidade" value="<?php echo $values['city'] ?? ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Estado</label>
                                    <select name="state" class="form-select">
                                        <option value="">UF</option>
                                        <?php foreach (['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SE','SP','TO'] as $uf): ?>
                                            <option value="<?php echo $uf; ?>" <?php echo (isset($values['state']) && $values['state'] === $uf) ? 'selected' : ''; ?>><?php echo $uf; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <!--
                            <div class="form-row full">
                                <div class="form-group">
                                    <label class="form-label">Foto da Empresa</label>
                                    <div class="upload-area" id="uploadArea">
                                        <div class="upload-icon">📷</div>
                                        <div class="upload-text">Clique para enviar imagem (opcional)</div>
                                    </div>
                                    <input type="file" id="companyPhoto" name="companyPhoto" accept="image/*" style="display:none;">
                                </div>
                            </div>
                            -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Número de Funcionários</label>
                                    <select name="numberEmployees" class="form-select">
                                        <option value="">Selecione</option>
                                        <option value="1-5" <?php echo (isset($values['numberEmployees']) && $values['numberEmployees'] === '1-5') ? 'selected' : ''; ?>>1-5</option>
                                        <option value="6-20" <?php echo (isset($values['numberEmployees']) && $values['numberEmployees'] === '6-20') ? 'selected' : ''; ?>>6-20</option>
                                        <option value="21-50" <?php echo (isset($values['numberEmployees']) && $values['numberEmployees'] === '21-50') ? 'selected' : ''; ?>>21-50</option>
                                        <option value="51-100" <?php echo (isset($values['numberEmployees']) && $values['numberEmployees'] === '51-100') ? 'selected' : ''; ?>>51-100</option>
                                        <option value="100+" <?php echo (isset($values['numberEmployees']) && $values['numberEmployees'] === '100+') ? 'selected' : ''; ?>>100+</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Faixa de Faturamento</label>
                                    <select name="revenueRange" class="form-select">
                                        <option value="">Selecione</option>
                                        <option value="até 250k" <?php echo (isset($values['revenueRange']) && $values['revenueRange'] === 'até 250k') ? 'selected' : ''; ?>>Até R$250k</option>
                                        <option value="250k-1m" <?php echo (isset($values['revenueRange']) && $values['revenueRange'] === '250k-1m') ? 'selected' : ''; ?>>R$250k - R$1M</option>
                                        <option value="1m-5m" <?php echo (isset($values['revenueRange']) && $values['revenueRange'] === '1m-5m') ? 'selected' : ''; ?>>R$1M - R$5M</option>
                                        <option value="5m+" <?php echo (isset($values['revenueRange']) && $values['revenueRange'] === '5m+') ? 'selected' : ''; ?>>R$5M+</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group full">
                                <label class="form-label">Categorias de Interesse</label>
                                <textarea name="interestedCategories" class="form-textarea" placeholder="Ex: Alimentação, Limpeza, Distribuição"><?php echo $values['interestedCategories'] ?? ''; ?></textarea>
                            </div>
                            <div class="form-group full">
                                <label class="form-label">Produtos que Costumam Comprar</label>
                                <textarea name="productsPurchased" class="form-textarea" placeholder="Ex: Ingredientes, embalagens, móveis comerciais"><?php echo $values['productsPurchased'] ?? ''; ?></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Frequência de Compra</label>
                                    <select name="purchaseFrequency" class="form-select">
                                        <option value="">Selecione</option>
                                        <option value="semanal" <?php echo (isset($values['purchaseFrequency']) && $values['purchaseFrequency'] === 'semanal') ? 'selected' : ''; ?>>Semanal</option>
                                        <option value="quinzenal" <?php echo (isset($values['purchaseFrequency']) && $values['purchaseFrequency'] === 'quinzenal') ? 'selected' : ''; ?>>Quinzenal</option>
                                        <option value="mensal" <?php echo (isset($values['purchaseFrequency']) && $values['purchaseFrequency'] === 'mensal') ? 'selected' : ''; ?>>Mensal</option>
                                        <option value="trimestral" <?php echo (isset($values['purchaseFrequency']) && $values['purchaseFrequency'] === 'trimestral') ? 'selected' : ''; ?>>Trimestral</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Termos de Uso <span class="required">*</span></label>
                                    <label class="checkbox-wrapper">
                                        <input type="checkbox" name="termsAccepted" value="1" <?php echo !empty($_POST['termsAccepted']) ? 'checked' : ''; ?>>
                                        <span>Li e aceito os termos de uso</span>
                                    </label>
                                    <div class="field-error"><?php echo $errors['termsAccepted'] ?? ''; ?></div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-action btn-back" onclick="previousStep(1);">← Voltar</button>
                                <button type="submit" class="btn-action btn-submit">Finalizar Cadastro</button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="step-section" data-step="2" style="display:none;">
                            <div class="section-header">
                                <h2 class="section-title">Dados da sua empresa</h2>
                                <p class="section-description">Complete as informações para criar um perfil comercial claro e alinhado aos compradores corporativos.</p>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">CNPJ <span class="required">*</span></label>
                                    <input type="text" name="cnpj" class="form-input cnpj-input" placeholder="00.000.000/0000-00" inputmode="numeric" maxlength="18" value="<?php echo $values['cnpj'] ?? ''; ?>" required>
                                    <div class="cnpj-counter" data-cnpj-counter>0/14</div>
                                    <div class="cnpj-debug-log" data-cnpj-debug role="status" aria-live="polite"><?php echo safe($errors['cnpj'] ?? ''); ?></div>
                                    <div class="field-error"><?php echo $errors['cnpj'] ?? ''; ?></div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Razão Social <span class="required">*</span></label>
                                    <input type="text" name="companyLegalName" class="form-input" placeholder="Razão social" value="<?php echo $values['companyLegalName'] ?? ''; ?>" required>
                                    <div class="field-error"><?php echo $errors['companyLegalName'] ?? ''; ?></div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Telefone <span class="required">*</span></label>
                                    <input type="tel" name="phone" class="form-input" placeholder="(00) 00000-0000" value="<?php echo $values['phone'] ?? ''; ?>" required>
                                    <div class="field-error"><?php echo $errors['phone'] ?? ''; ?></div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Segmento de Atuação</label>
                                    <input type="text" name="segment" class="form-input" placeholder="Ex: Manufatura, Distribuição, Serviços" value="<?php echo $values['segment'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Cidade</label>
                                    <input type="text" name="city" class="form-input" placeholder="Sua cidade" value="<?php echo $values['city'] ?? ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Estado</label>
                                    <select name="state" class="form-select">
                                        <option value="">UF</option>
                                        <?php foreach (['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SE','TO'] as $uf): ?>
                                            <option value="<?php echo $uf; ?>" <?php echo (isset($values['state']) && $values['state'] === $uf) ? 'selected' : ''; ?>><?php echo $uf; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-action btn-back" onclick="previousStep(1);">← Voltar</button>
                                <button type="button" class="btn-action btn-submit" onclick="nextStep(3);">Próximo →</button>
                            </div>
                        </div>
                        <div class="step-section" data-step="3" style="display:none;">
                            <!--
                            <div class="form-row full">
                                <div class="form-group">
                                    <label class="form-label">Logotipo da Empresa</label>
                                    <div class="upload-area" id="logoArea">
                                        <div class="upload-icon">📷</div>
                                        <div class="upload-text">Clique para enviar o logotipo</div>
                                    </div>
                                    <input type="file" id="companyLogo" name="companyLogo" accept="image/*" style="display:none;">
                                </div>
                            </div>
                            <div class="form-row full">
                                <div class="form-group">
                                    <label class="form-label">Imagem de Capa</label>
                                    <div class="upload-area" id="coverArea">
                                        <div class="upload-icon">🖼️</div>
                                        <div class="upload-text">Clique para enviar a imagem de capa</div>
                                    </div>
                                    <input type="file" id="coverImage" name="coverImage" accept="image/*" style="display:none;">
                                </div>
                            </div>
                            -->

                            </div>
                            <div class="form-group full">
                                <label class="form-label">Descrição da Empresa <span class="required">*</span></label>
                                <textarea name="companyDescription" class="form-textarea" placeholder="Conte como sua empresa atende outros negócios"><?php echo $values['companyDescription'] ?? ''; ?></textarea>
                                <div class="field-error"><?php echo $errors['companyDescription'] ?? ''; ?></div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Categoria</label>
                                    <input type="text" name="category" class="form-input" placeholder="Ex: Alimentação, Logística, Saúde" value="<?php echo $values['category'] ?? ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Principais Produtos</label>
                                    <textarea name="mainProducts" class="form-textarea" placeholder="Ex: Snacks, bebidas, materiais de limpeza"><?php echo $values['mainProducts'] ?? ''; ?></textarea>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Região de Atendimento</label>
                                    <input type="text" name="serviceRegion" class="form-input" placeholder="Ex: São Paulo, Brasil" value="<?php echo $values['serviceRegion'] ?? ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Site</label>
                                    <input type="url" name="website" class="form-input" placeholder="https://www.suaempresa.com" value="<?php echo $values['website'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="form-row full">
                                <div class="form-group">
                                    <label class="form-label">WhatsApp</label>
                                    <input type="text" name="whatsapp" class="form-input" placeholder="(00) 00000-0000" value="<?php echo $values['whatsapp'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-action btn-back" onclick="previousStep(2);">← Voltar</button>
                                <button type="button" class="btn-action btn-submit" onclick="nextStep(4);">Próximo →</button>
                            </div>
                        </div>
                        <div class="step-section" data-step="4" style="display:none;">
                            <div class="form-group full">
                                <label class="form-label">Plano de Assinatura <span class="required">*</span></label>
                                <select name="plan" class="form-select">
                                    <option value="">Escolha um plano</option>
                                    <option value="basic" <?php echo (isset($values['plan']) && $values['plan'] === 'basic') ? 'selected' : ''; ?>>Básico</option>
                                    <option value="professional" <?php echo (isset($values['plan']) && $values['plan'] === 'professional') ? 'selected' : ''; ?>>Profissional</option>
                                    <option value="premium" <?php echo (isset($values['plan']) && $values['plan'] === 'premium') ? 'selected' : ''; ?>>Premium</option>
                                </select>
                                <div class="field-error"><?php echo $errors['plan'] ?? ''; ?></div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-action btn-back" onclick="previousStep(3);">← Voltar</button>
                                <button type="submit" class="btn-action btn-submit">Finalizar Cadastro</button>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div id="confirmModal" class="modal-overlay">
        <div class="modal-content">
            <h2 class="modal-title">Alterar tipo de conta?</h2>
            <p class="modal-text">Seus dados de registro atual não serão salvos. Deseja retornar e escolher outro tipo de conta?</p>
            <div class="modal-actions">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeConfirmModal();">Cancelar</button>
                <button type="button" class="modal-btn modal-btn-confirm" onclick="executeChangeAccountType();">Continuar</button>
            </div>
        </div>
    </div>

    <script>
        const wizardSteps = Array.from(document.querySelectorAll('.wizard-step'));
        const sections = Array.from(document.querySelectorAll('.step-section'));
        const currentStepInput = document.getElementById('currentStep');
        const registerForm = document.getElementById('registerForm');
        const confirmModal = document.getElementById('confirmModal');
        const initialStep = <?php echo json_encode($step); ?>;
        const cnpjInputs = document.querySelectorAll('.cnpj-input');

        function isValidCnpjClient(value) {
            const digits = value.replace(/\D/g, '');
            if (digits.length !== 14 || /^(\d)\1{13}$/.test(digits)) return false;

            const weights = [
                [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
                [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
            ];

            return weights.every((roundWeights, round) => {
                const length = 12 + round;
                const sum = roundWeights.reduce((total, weight, index) => {
                    return total + Number(digits[index]) * weight;
                }, 0);
                const remainder = sum % 11;
                const checkDigit = remainder < 2 ? 0 : 11 - remainder;
                return Number(digits[length]) === checkDigit;
            });
        }

        function formatCnpjClient(value) {
            const digits = value.replace(/\D/g, '').slice(0, 14);
            return digits
                .replace(/^(\d{2})(\d)/, '$1.$2')
                .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/^(\d{2})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3/$4')
                .replace(/^(\d{2})\.(\d{3})\.(\d{3})\/(\d{4})(\d)/, '$1.$2.$3/$4-$5');
        }

        const cnpjRequestIds = new WeakMap();
        const cnpjStatuses = new WeakMap();
        const cnpjAbortControllers = new WeakMap();
        const cnpjRetryAttempts = new WeakMap();
        const cnpjRetryTimers = new WeakMap();
        const cnpjValidationCacheKey = 'nexar_cnpj_validation_';

        function getCachedCnpjResult(digits) {
            try {
                const cached = sessionStorage.getItem(cnpjValidationCacheKey + digits);
                return cached ? JSON.parse(cached) : null;
            } catch (error) {
                return null;
            }
        }

        function cacheCnpjResult(digits, data) {
            try {
                sessionStorage.setItem(cnpjValidationCacheKey + digits, JSON.stringify(data));
            } catch (error) {
                // O cache é opcional; a confirmação atual continua em memória.
            }
        }

        async function checkCnpjExists(input, digits) {
            const currentStatus = cnpjStatuses.get(input);
            if (currentStatus?.digits === digits && currentStatus.validated) return true;

            const cachedResult = getCachedCnpjResult(digits);
            if (cachedResult?.validated === true) {
                cnpjStatuses.set(input, cachedResult);
                input.readOnly = true;
                return true;
            }

            const requestId = (cnpjRequestIds.get(input) || 0) + 1;
            cnpjRequestIds.set(input, requestId);
            cnpjStatuses.set(input, { digits, exists: false, checking: false });
            const debugLog = input.parentElement.querySelector('[data-cnpj-debug]');
            if (!debugLog || digits.length !== 14 || !isValidCnpjClient(digits)) return false;

            const previousController = cnpjAbortControllers.get(input);
            if (previousController) previousController.abort();
            const controller = new AbortController();
            cnpjAbortControllers.set(input, controller);

            cnpjStatuses.set(input, { digits, exists: false, checking: true });
            debugLog.classList.remove('warning');
            debugLog.textContent = 'Consultando CNPJ na BrasilAPI...';
            try {
                const response = await fetch('/NEXAR/api/cnpj/validate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ cnpj: digits }),
                    signal: controller.signal,
                });
                const payload = await response.json();

                if (cnpjRequestIds.get(input) !== requestId) return false;
                const apiConfirmed = response.ok
                    && payload.success
                    && payload.data?.exists === true
                    && (payload.data?.api_status == null
                        || (payload.data.api_status >= 200 && payload.data.api_status < 300));
                if (!apiConfirmed) {
                    cnpjStatuses.set(input, { digits, exists: false, checking: false });
                    debugLog.classList.add('warning');

                    if (payload.data?.api_status === 429) {
                        const attempt = (cnpjRetryAttempts.get(input) || 0) + 1;
                        cnpjRetryAttempts.set(input, attempt);
                        const wait = Math.min(2000 * attempt, 10000);
                        debugLog.textContent = `Muitas consultas seguidas — tentando de novo em ${Math.round(wait / 1000)}s...`;
                        setTimeout(() => {
                            if (cnpjRequestIds.get(input) === requestId
                                && input.value.replace(/\D/g, '') === digits) {
                                checkCnpjExists(input, digits);
                            }
                        }, wait);
                    } else if (payload.data?.api_status === 404) {
                        cnpjRetryAttempts.delete(input);
                        debugLog.textContent = 'Aviso: este CNPJ não existe na BrasilAPI.';
                    } else {
                        cnpjRetryAttempts.delete(input);
                        debugLog.textContent = 'Erro: a BrasilAPI não confirmou este CNPJ.';
                    }
                    return false;
                }

                cnpjRetryAttempts.delete(input);
                const validatedResult = {
                    digits,
                    exists: true,
                    checking: false,
                    validated: true,
                    apiResult: payload.data,
                };
                cnpjStatuses.set(input, validatedResult);
                cacheCnpjResult(digits, validatedResult);
                input.readOnly = true;
                debugLog.classList.remove('warning');
                debugLog.textContent = '';
                return true;
            } catch (error) {
                if (error.name === 'AbortError') return false;
                if (cnpjRequestIds.get(input) === requestId) {
                    cnpjStatuses.set(input, { digits, exists: false, checking: false });
                    debugLog.textContent = 'Erro: não foi possível consultar a BrasilAPI.';
                }
                return false;
            }
        }

        async function ensureCnpjExists(input) {
            const digits = input.value.replace(/\D/g, '');
            const status = cnpjStatuses.get(input);

            if (status?.digits === digits && status.exists) return true;
            if (status?.digits === digits && status.checking) {
                return new Promise((resolve) => {
                    const waitForResult = () => {
                        const currentStatus = cnpjStatuses.get(input);
                        if (currentStatus?.digits !== digits || !currentStatus?.checking) {
                            resolve(Boolean(currentStatus?.exists && currentStatus.digits === digits));
                            return;
                        }
                        window.setTimeout(waitForResult, 100);
                    };
                    waitForResult();
                });
            }

            return checkCnpjExists(input, digits);
        }

        function updateCnpjFeedback(input) {
            const digits = input.value.replace(/\D/g, '');
            const counter = input.parentElement.querySelector('[data-cnpj-counter]');
            const debugLog = input.parentElement.querySelector('[data-cnpj-debug]');

            if (counter) counter.textContent = `${digits.length}/14`;
            if (!debugLog) return;

            if (digits.length === 0) {
                debugLog.textContent = '';
            } else if (digits.length < 14) {
                debugLog.textContent = `Erro: CNPJ incompleto (${digits.length}/14).`;
            } else if (!isValidCnpjClient(digits)) {
                debugLog.textContent = 'Erro: dígitos verificadores inválidos.';
            } else {
                debugLog.textContent = '';
            }
        }

        cnpjInputs.forEach((input) => {
            input.value = formatCnpjClient(input.value);
            updateCnpjFeedback(input);
            const initialDigits = input.value.replace(/\D/g, '');
            const cachedResult = initialDigits.length === 14
                ? getCachedCnpjResult(initialDigits)
                : null;
            if (cachedResult?.validated === true) {
                cnpjStatuses.set(input, cachedResult);
                input.readOnly = true;
            }

            input.addEventListener('input', () => {
                if (input.readOnly) return;
                const digits = input.value.replace(/\D/g, '').slice(0, 14);
                input.value = formatCnpjClient(digits);
                input.setCustomValidity('');
                updateCnpjFeedback(input);
            });

            input.addEventListener('blur', () => {
                if (input.readOnly) return;
                const digits = input.value.replace(/\D/g, '');
                if (digits.length === 14 && isValidCnpjClient(digits)) {
                    checkCnpjExists(input, digits);
                }
            });
            input.addEventListener('paste', (event) => {
                if (input.readOnly) return;
                event.preventDefault();
                const pastedText = event.clipboardData?.getData('text') || '';
                input.value = pastedText.replace(/\D/g, '').slice(0, 14);
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });
        });

        // ===== ACCOUNT TYPE SELECTION LOGIC =====
        const accountTypeRadios = document.querySelectorAll('input[type="radio"][name="account_type"]');
        const accountTypeSelectionGrid = document.querySelector('.account-type-grid');
        const submitButton = accountTypeSelectionGrid?.querySelector('button[type="submit"]');
        const cardEntrepreneur = document.getElementById('cardEntrepreneur');
        const cardSupplier = document.getElementById('cardSupplier');

        function initAccountTypeSelection() {
            // Only initialize on the account type selection screen
            if (!accountTypeSelectionGrid || !accountTypeRadios.length) return;

            // Add event listeners to radio buttons
            accountTypeRadios.forEach(radio => {
                radio.addEventListener('change', (e) => {
                    // Remove selected class from all cards
                    cardEntrepreneur?.classList.remove('selected');
                    cardSupplier?.classList.remove('selected');

                    // Add selected class to the card of the selected radio
                    if (e.target.value === 'entrepreneur') {
                        cardEntrepreneur?.classList.add('selected');
                    } else if (e.target.value === 'supplier') {
                        cardSupplier?.classList.add('selected');
                    }

                    // Enable submit button if any option is selected
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                });

                // Add click handler to cards for better UX
                const cardLabel = radio.closest('.account-type-card');
                if (cardLabel) {
                    cardLabel.addEventListener('click', (e) => {
                        e.preventDefault();
                        radio.checked = true;
                        radio.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                }
            });

            // Initially check if any option is pre-selected (from form submission error)
            const checkedRadio = document.querySelector('input[name="account_type"]:checked');
            if (checkedRadio) {
                checkedRadio.dispatchEvent(new Event('change', { bubbles: true }));
            } else {
                // Disable button until selection is made
                if (submitButton) {
                    submitButton.disabled = true;
                }
            }

            // Add form submission validation
            if (registerForm) {
                registerForm.addEventListener('submit', (e) => {
                    const selectedAccountType = document.querySelector('input[type="radio"][name="account_type"]:checked')
                        || document.querySelector('input[type="hidden"][name="account_type"][value]');
                    if (!selectedAccountType) {
                        e.preventDefault();
                        alert('Por favor, selecione um tipo de conta antes de continuar.');
                        return false;
                    }
                });
            }
        }

        function showStep(step) {
            sections.forEach(section => {
                section.style.display = section.dataset.step === String(step) ? 'block' : 'none';
            });
            wizardSteps.forEach(tab => {
                tab.classList.toggle('active', tab.dataset.step === String(step));
            });
            if (currentStepInput) currentStepInput.value = step;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        async function nextStep(targetStep) {
            const visibleSection = sections.find(section => section.style.display === 'block');
            if (visibleSection) {
                const invalidField = Array.from(visibleSection.querySelectorAll('input, select, textarea'))
                    .find(field => field.required && !field.value.trim());

                if (invalidField) {
                    invalidField.focus();
                    invalidField.reportValidity?.();
                    alert('Por favor, preencha todos os campos obrigatórios antes de avançar.');
                    return;
                }

                const cnpjInput = visibleSection.querySelector('.cnpj-input');
                if (cnpjInput && !isValidCnpjClient(cnpjInput.value)) {
                    const debugLog = cnpjInput.parentElement.querySelector('[data-cnpj-debug]');
                    if (debugLog) debugLog.textContent = 'Erro: CNPJ inválido. Confira os 14 dígitos.';
                    cnpjInput.setCustomValidity('Informe um CNPJ válido com 14 dígitos.');
                    cnpjInput.focus();
                    cnpjInput.reportValidity?.();
                    return;
                }

                if (cnpjInput && !(await ensureCnpjExists(cnpjInput))) {
                    const debugLog = cnpjInput.parentElement.querySelector('[data-cnpj-debug]');
                    if (debugLog) {
                        debugLog.classList.add('warning');
                        debugLog.textContent = 'Aviso: confirme um CNPJ existente na BrasilAPI para continuar.';
                    }
                    cnpjInput.focus();
                    return;
                }
            }

            // Save current form data
            const formData = new FormData(registerForm);
            const action = formData.get('action');
            
            // Create a hidden form submission
            const hiddenForm = document.createElement('form');
            hiddenForm.method = 'POST';
            
            // Copy all form data
            for (let [key, value] of formData.entries()) {
                if (key === 'action') {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'action';
                    input.value = 'update_step';
                    hiddenForm.appendChild(input);
                } else {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    hiddenForm.appendChild(input);
                }
            }
            
            // Add step
            const stepInput = document.createElement('input');
            stepInput.type = 'hidden';
            stepInput.name = 'step';
            stepInput.value = targetStep;
            hiddenForm.appendChild(stepInput);
            
            document.body.appendChild(hiddenForm);
            hiddenForm.submit();
        }

        function previousStep(targetStep) {
            const formData = new FormData(registerForm);
            const hiddenForm = document.createElement('form');
            hiddenForm.method = 'POST';

            for (let [key, value] of formData.entries()) {
                if (key === 'action') {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'action';
                    input.value = 'update_step';
                    hiddenForm.appendChild(input);
                } else {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    hiddenForm.appendChild(input);
                }
            }

            const stepInput = document.createElement('input');
            stepInput.type = 'hidden';
            stepInput.name = 'step';
            stepInput.value = targetStep;
            hiddenForm.appendChild(stepInput);

            document.body.appendChild(hiddenForm);
            hiddenForm.submit();
        }

        function confirmChangeAccountType(event, hasData) {
            if (event) {
                event.preventDefault();
            }
            
            if (hasData) {
                confirmModal.classList.add('show');
            } else {
                window.location.href = '/NEXAR/register';
            }
        }

        function closeConfirmModal() {
            confirmModal.classList.remove('show');
        }

        function executeChangeAccountType() {
            closeConfirmModal();
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="action" value="change_account_type">';
            document.body.appendChild(form);
            form.submit();
        }

        // Close modal on background click
        confirmModal.addEventListener('click', (e) => {
            if (e.target === confirmModal) {
                closeConfirmModal();
            }
        });

        /*
        [[ 'uploadArea', 'companyPhoto' ], [ 'logoArea', 'companyLogo' ], [ 'coverArea', 'coverImage' ]].forEach(([areaId, inputId]) => {
            const area = document.getElementById(areaId);
            const input = document.getElementById(inputId);
            if (!area || !input) return;
            area.addEventListener('click', () => input.click());
            input.addEventListener('change', (event) => {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = ({ target }) => {
                    area.innerHTML = `<img src="${target.result}" style="max-width: 100%; max-height: 200px; border-radius: 16px;">`;
                };
                reader.readAsDataURL(file);
            });
        });
        */

        // Initialize account type selection logic
        initAccountTypeSelection();
        showStep(initialStep);
    </script>
</body>
</html>
