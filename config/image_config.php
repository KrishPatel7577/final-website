<?php
function getProductImageUrl($productId, $category, $defaultImageUrl = '') {
    // Path to image sources JSON
    $imageSourcesPath = __DIR__ . '/../pics/image_sources.json';
    
    // If default image URL is provided and valid, use it
    if (!empty($defaultImageUrl) && filter_var($defaultImageUrl, FILTER_VALIDATE_URL)) {
        return $defaultImageUrl;
    }
    
    // Load image sources from JSON
    if (file_exists($imageSourcesPath)) {
        $imageData = json_decode(file_get_contents($imageSourcesPath), true);
        
        if (isset($imageData['image_sources'][$category][$productId])) {
            return $imageData['image_sources'][$category][$productId];
        }
        
        // Use category fallback
        if (isset($imageData['fallback_images'][$category])) {
            return $imageData['fallback_images'][$category];
        }
    }
    
    // Ultimate fallback
    // Ultimate fallback
    return 'https://placehold.co/500x500/e2e8f0/1e293b?text=Product+Image';
}

function getAllImageSources() {
    $imageSourcesPath = __DIR__ . '/../pics/image_sources.json';
    
    if (file_exists($imageSourcesPath)) {
        return json_decode(file_get_contents($imageSourcesPath), true);
    }
    
    return null;
}

function updateProductImage($productId, $category, $imageUrl) {
    $imageSourcesPath = __DIR__ . '/../pics/image_sources.json';
    
    if (!file_exists($imageSourcesPath)) {
        return false;
    }
    
    $imageData = json_decode(file_get_contents($imageSourcesPath), true);
    
    if (!isset($imageData['image_sources'][$category])) {
        $imageData['image_sources'][$category] = [];
    }
    
    $imageData['image_sources'][$category][$productId] = $imageUrl;
    
    return file_put_contents($imageSourcesPath, json_encode($imageData, JSON_PRETTY_PRINT)) !== false;
}

?>

