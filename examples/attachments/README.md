# Attachments Examples

This folder demonstrates how to add file attachments to emails using WebFiori Mailer.

## Examples

### 📎 file-attachments.php
Comprehensive example showing different attachment methods:
- Attach files using file paths (strings)
- Attach files using File objects
- Use fluent interface for attachments
- Display attachment information

### 🛠️ create-sample-files.php
Utility script to create sample files for testing:
- Creates sample PDF, text, image, and CSV files
- Sets up the sample-files directory structure

## Setup

Before running the attachment examples, create sample files:

```bash
cd examples/attachments
php create-sample-files.php
```

This will create a `sample-files/` directory with:
- `document.pdf` - Sample PDF document
- `readme.txt` - Text file with information
- `image.jpg` - Small sample image
- `contacts.csv` - Sample CSV data

## Attachment Methods

### Method 1: File Path (String)
```php
$email->addAttachment('/path/to/file.pdf');
```

### Method 2: File Object
```php
use webfiori\file\File;

$file = new File('/path/to/file.pdf');
$email->addAttachment($file);
```

### Method 3: Fluent Interface
```php
$email->attach('/path/to/file.pdf');
```

## Supported File Types

WebFiori Mailer supports attaching various file types:
- **Documents**: PDF, DOC, DOCX, TXT
- **Images**: JPG, PNG, GIF, BMP
- **Data**: CSV, JSON, XML
- **Archives**: ZIP, RAR
- **Any file type** with proper MIME detection

## File Size Considerations

### Email Provider Limits
- **Gmail**: 25MB total attachment size
- **Outlook**: 20MB total attachment size
- **Yahoo**: 25MB total attachment size
- **Custom SMTP**: Varies by provider

### Best Practices
- Keep total attachment size under 10MB for better deliverability
- Use cloud storage links for large files
- Compress files when possible
- Consider multiple emails for many attachments

## Attachment Information

### Getting Attachment Details
```php
$attachments = $email->getAttachments();

foreach ($attachments as $attachment) {
    echo "Name: " . $attachment->getName() . "\n";
    echo "Size: " . $attachment->getSize() . " bytes\n";
    echo "MIME: " . $attachment->getMIMEType() . "\n";
}
```

### Attachment Count
```php
$count = count($email->getAttachments());
echo "Total attachments: " . $count;
```

## Running the Examples

```bash
# Navigate to attachments directory
cd examples/attachments

# Create sample files first
php create-sample-files.php

# Run the attachment example
php file-attachments.php
```

## Error Handling

### Common Issues
- **File not found**: Check file paths and permissions
- **File too large**: Verify size limits with your email provider
- **Permission denied**: Ensure read access to attachment files

### Example Error Handling
```php
try {
    if (file_exists($filePath)) {
        $email->addAttachment($filePath);
    } else {
        throw new Exception("Attachment file not found: " . $filePath);
    }
    
    $email->send();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## Security Considerations

### File Validation
- Validate file types before attaching
- Check file sizes to prevent abuse
- Scan files for malware if accepting user uploads

### Safe Practices
```php
// Validate file extension
$allowedTypes = ['pdf', 'txt', 'jpg', 'png'];
$extension = pathinfo($filePath, PATHINFO_EXTENSION);

if (in_array(strtolower($extension), $allowedTypes)) {
    $email->addAttachment($filePath);
} else {
    throw new Exception("File type not allowed: " . $extension);
}
```

## Performance Tips

- **Lazy Loading**: Only load attachments when needed
- **Caching**: Cache file information for repeated sends
- **Compression**: Use compressed formats when possible
- **Async Processing**: Consider background processing for large attachments
