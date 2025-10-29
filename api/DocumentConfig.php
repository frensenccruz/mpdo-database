<?php
/**
 * Document Configuration Manager
 * Centralizes all document type rules and validation
 */
class DocumentConfig {
    
    /**
     * Get field configuration for each document type
     */
    public static function getFieldConfig($docType) {
        $configs = [
            'Certification' => [
                'fields' => ['name', 'subject'],
                'required' => ['name', 'subject'],
                'labels' => [
                    'name' => 'Name',
                    'subject' => 'Subject'
                ]
            ],
            'Reclassification' => [
                'fields' => ['name', 'subject', 'category'],
                'required' => ['name', 'subject', 'category'],
                'labels' => [
                    'name' => 'Name',
                    'subject' => 'Subject',
                    'category' => 'Category'
                ],
                'options' => [
                    'category' => ['Residential', 'Agriculture', 'Commercial', 'Industrial', 'Light Industrial', 'Institutions']
                ]
            ],
            'Endorsement' => [
                'fields' => ['name', 'subject', 'category'],
                'required' => ['name', 'subject', 'category'],
                'labels' => [
                    'name' => 'Name',
                    'subject' => 'Subject',
                    'category' => 'Type'
                ],
                'options' => [
                    'category' => ['Reclassification', 'Hosting']
                ]
            ],
            'Resolution' => [
                'fields' => ['subject', 'number'],
                'required' => ['subject', 'number'],
                'labels' => [
                    'subject' => 'Subject',
                    'number' => 'Resolution Number'
                ]
            ],
            'Ordinance' => [
                'fields' => ['subject', 'number'],
                'required' => ['subject', 'number'],
                'labels' => [
                    'subject' => 'Subject',
                    'number' => 'Ordinance Number'
                ]
            ],
            'Reprogramming' => [
                'fields' => ['subject', 'number'],
                'required' => ['subject', 'number'],
                'labels' => [
                    'subject' => 'Subject',
                    'number' => 'Number'
                ]
            ],
            '20%' => [
                'fields' => ['subject', 'number'],
                'required' => ['subject', 'number'],
                'labels' => [
                    'subject' => 'Subject',
                    'number' => 'Number'
                ]
            ],
            'Correspondence' => [
                'fields' => ['direction', 'name', 'department', 'subject'],
                'required' => ['direction', 'name', 'department', 'subject'],
                'labels' => [
                    'direction' => 'Direction',
                    'name' => 'Name',
                    'department' => 'Department',
                    'subject' => 'Subject'
                ],
                'options' => [
                    'direction' => ['Incoming', 'Outgoing']
                ]
            ]
        ];
        
        return $configs[$docType] ?? null;
    }
    
    /**
     * Validate document data based on type
     */
    public static function validate($docType, $data) {
        $config = self::getFieldConfig($docType);
        
        if (!$config) {
            throw new Exception('Invalid document type.');
        }
        
        $errors = [];
        
        // Check required fields
        foreach ($config['required'] as $field) {
            if (empty(trim($data[$field] ?? ''))) {
                $label = $config['labels'][$field] ?? $field;
                $errors[] = "$label is required.";
            }
        }
        
        // Validate options if applicable
        if (isset($config['options'])) {
            foreach ($config['options'] as $field => $validOptions) {
                if (!empty($data[$field]) && !in_array($data[$field], $validOptions)) {
                    $label = $config['labels'][$field] ?? $field;
                    $errors[] = "Invalid value for $label.";
                }
            }
        }
        
        if (!empty($errors)) {
            throw new Exception(implode(' ', $errors));
        }
        
        return true;
    }
    
    /**
     * Extract and clean data for a specific document type
     */
    public static function extractData($docType, $postData) {
        $config = self::getFieldConfig($docType);
        
        if (!$config) {
            return [];
        }
        
        $data = [];
        foreach ($config['fields'] as $field) {
            $value = trim($postData[$field] ?? '');
            if ($value !== '') {
                $data[$field] = $value;
            }
        }
        
        return $data;
    }
    
    /**
     * Get all document types
     */
    public static function getTypes() {
        return [
            'Certification',
            'Reclassification',
            'Endorsement',
            'Resolution',
            'Ordinance',
            'Reprogramming',
            '20%',
            'Correspondence'
        ];
    }
}