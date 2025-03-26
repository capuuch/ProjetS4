<?php
/**
 * Payment functions for CY Bank integration
 */

// Include the existing getAPIKey function
require_once 'getapikey.php';

/**
 * Generate a unique transaction ID
 * 
 * @return string Transaction ID (alphanumeric, 10-24 characters)
 */
function generateTransactionId() {
    // Generate a random string of 16 characters
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $transaction_id = '';
    for ($i = 0; $i < 16; $i++) {
        $transaction_id .= $chars[rand(0, strlen($chars) - 1)];
    }
    
    // Add timestamp to ensure uniqueness
    $transaction_id .= substr(time(), -4);
    
    return $transaction_id;
}

/**
 * Calculate control value for CY Bank payment
 * 
 * @param string $transaction Transaction ID
 * @param float $montant Payment amount
 * @param string $vendeur Vendor code
 * @param string $retour Return URL
 * @return string Control value (MD5 hash)
 */
function calculateControlValue($transaction, $montant, $vendeur, $retour) {
    $api_key = getAPIKey($vendeur);
    
    // Format according to the hashing rule
    $control_string = $api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $retour . "#";
    
    // Generate MD5 hash
    return md5($control_string);
}

/**
 * Verify control value from CY Bank return
 * 
 * @param string $transaction Transaction ID
 * @param float $montant Payment amount
 * @param string $vendeur Vendor code
 * @param string $retour Return URL
 * @param string $control Control value to verify
 * @return bool True if control value is valid
 */
function verifyControlValue($transaction, $montant, $vendeur, $retour, $control) {
    $calculated_control = calculateControlValue($transaction, $montant, $vendeur, $retour);
    return $calculated_control === $control;
}

/**
 * Format amount for CY Bank (2 decimal places with dot separator)
 * 
 * @param float $amount Amount to format
 * @return string Formatted amount
 */
function formatAmount($amount) {
    return number_format($amount, 2, '.', '');
}

/**
 * Save payment transaction to database or file
 * 
 * @param string $transaction Transaction ID
 * @param float $montant Payment amount
 * @param string $vendeur Vendor code
 * @param string $username Username
 * @param array $cart_items Cart items
 * @return bool True if saved successfully
 */
function savePaymentTransaction($transaction, $montant, $vendeur, $username, $cart_items) {
    $transactions_file = '../json/transactions.json';
    
    // Load existing transactions
    $transactions = file_exists($transactions_file) ? 
        json_decode(file_get_contents($transactions_file), true) : [];
    
    if (!is_array($transactions)) {
        $transactions = [];
    }
    
    // Create new transaction record
    $transactions[] = [
        'transaction_id' => $transaction,
        'amount' => $montant,
        'vendor' => $vendeur,
        'username' => $username,
        'cart_items' => $cart_items,
        'status' => 'pending',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Save transactions
    return file_put_contents($transactions_file, json_encode($transactions, JSON_PRETTY_PRINT)) !== false;
}

/**
 * Update payment transaction status
 * 
 * @param string $transaction Transaction ID
 * @param string $status New status (accepted, refused)
 * @return bool True if updated successfully
 */
function updatePaymentStatus($transaction, $status) {
    $transactions_file = '../json/transactions.json';
    
    // Load existing transactions
    $transactions = file_exists($transactions_file) ? 
        json_decode(file_get_contents($transactions_file), true) : [];
    
    if (!is_array($transactions)) {
        return false;
    }
    
    // Find and update transaction
    $updated = false;
    foreach ($transactions as &$trans) {
        if ($trans['transaction_id'] === $transaction) {
            $trans['status'] = $status;
            $trans['updated_at'] = date('Y-m-d H:i:s');
            $updated = true;
            break;
        }
    }
    
    if (!$updated) {
        return false;
    }
    
    // Save transactions
    return file_put_contents($transactions_file, json_encode($transactions, JSON_PRETTY_PRINT)) !== false;
}

/**
 * Clear user's cart after successful payment
 * 
 * @param string $username Username
 * @return bool True if cleared successfully
 */
function clearUserCart($username) {
    $options_file = '../json/options.json';
    
    // Load options
    $options = file_exists($options_file) ? 
        json_decode(file_get_contents($options_file), true) : [];
    
    if (!is_array($options) || !isset($options[$username])) {
        return false;
    }
    
    // Clear user's options
    $options[$username] = [];
    
    // Save options
    return file_put_contents($options_file, json_encode($options, JSON_PRETTY_PRINT)) !== false;
}
?>
