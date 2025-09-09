<?php

// Create sample files for attachment examples

$sampleDir = __DIR__ . '/sample-files';

// Create sample-files directory if it doesn't exist
if (!is_dir($sampleDir)) {
    mkdir($sampleDir, 0755, true);
    echo "Created sample-files directory\n";
}

// Create sample PDF content (simple text file with .pdf extension for demo)
$pdfContent = "%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj
2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj
3 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
/Contents 4 0 R
>>
endobj
4 0 obj
<<
/Length 44
>>
stream
BT
/F1 12 Tf
100 700 Td
(Sample PDF Document) Tj
ET
endstream
endobj
xref
0 5
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
0000000206 00000 n 
trailer
<<
/Size 5
/Root 1 0 R
>>
startxref
299
%%EOF";

file_put_contents($sampleDir . '/document.pdf', $pdfContent);
echo "Created sample document.pdf\n";

// Create sample text file
$textContent = "WebFiori Mailer - File Attachment Example
==========================================

This is a sample text file used to demonstrate file attachments in WebFiori Mailer.

Features demonstrated:
- Attaching files using file paths
- Attaching files using File objects
- Using fluent interface for attachments
- Multiple attachment types

For more information, visit: https://github.com/WebFiori/mail
";

file_put_contents($sampleDir . '/readme.txt', $textContent);
echo "Created sample readme.txt\n";

// Create a simple image file (1x1 pixel PNG)
$imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChAI9jU8j8wAAAABJRU5ErkJggg==');
file_put_contents($sampleDir . '/image.jpg', $imageData);
echo "Created sample image.jpg\n";

// Create CSV file
$csvContent = "Name,Email,Department
John Doe,john@example.com,Engineering
Jane Smith,jane@example.com,Marketing
Bob Johnson,bob@example.com,Sales
Alice Brown,alice@example.com,HR";

file_put_contents($sampleDir . '/contacts.csv', $csvContent);
echo "Created sample contacts.csv\n";

echo "\nAll sample files created successfully!\n";
echo "You can now run the attachment examples.\n";
