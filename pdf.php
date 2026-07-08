<?php
// pdf.php

require_once 'app/controllers/NikahController.php';
require_once 'app/helpers/session.php';

// Guards
require_login();

$controller = new NikahController();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("No certificate ID provided.");
}

$id = intval($_GET['id']);
$cert = $controller->show($id); // Redirects to dashboard if not found
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export PDF - <?php echo sanitize($cert['certificate_no']); ?></title>
    <!-- Custom A4 Print stylesheet -->
    <link rel="stylesheet" href="assets/css/print.css">
    <style>
        /* Ensure controls are completely hidden */
        .no-print {
            display: none !important;
        }
    </style>
</head>
<body>

    <!-- Official Certificate Wrapper -->
    <div class="certificate-wrapper">
        
        <!-- Double Border (Islamic Government Style) -->
        <div class="certificate-border">
            <div class="certificate-inner-border">
                
                <!-- Watermark Background -->
                <div class="certificate-watermark"></div>
                
                <!-- Arabic Bismillah & Title Header -->
                <div class="cert-header">
                    <div class="bismillah">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div>
                    <div class="govt-title">Islamic Republic of Bangladesh</div>
                    <div class="cert-title-bn">নিকাহনামা</div>
                    <div class="cert-title-en">Marriage Certificate</div>
                </div>
                
                <!-- Certificate Metas -->
                <div class="cert-meta-row">
                    <div class="cert-meta-item">
                        Certificate No: <span><?php echo sanitize($cert['certificate_no']); ?></span>
                    </div>
                    <div class="cert-meta-item">
                        Registration No: <span><?php echo sanitize($cert['registration_no']); ?></span>
                    </div>
                    <div class="cert-meta-item">
                        Issue Date: <span><?php echo date('Y-m-d'); ?></span>
                    </div>
                </div>
                
                <!-- GROOM SECTION -->
                <div class="cert-section">
                    <div class="section-divider">1. Bridegroom Details (বর)</div>
                    <div class="cert-grid">
                        <div class="cert-field">
                            <span class="cert-field-label">Name:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['groom_name']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">Phone:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['groom_phone']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">Father's Name:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['groom_father']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">Mother's Name:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['groom_mother']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">Date of Birth:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['groom_birth']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">NID / Passport:</span>
                            <span class="cert-field-value">
                                <?php 
                                    $g_nid = sanitize($cert['groom_nid']);
                                    $g_pass = sanitize($cert['groom_passport']);
                                    echo !empty($g_nid) ? 'NID: ' . $g_nid : (!empty($g_pass) ? 'Passport: ' . $g_pass : 'N/A'); 
                                ?>
                            </span>
                        </div>
                        <div class="cert-field" style="grid-column: span 2;">
                            <span class="cert-field-label">Address:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['groom_address']); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- BRIDE SECTION -->
                <div class="cert-section">
                    <div class="section-divider">2. Bride Details (কনে)</div>
                    <div class="cert-grid">
                        <div class="cert-field">
                            <span class="cert-field-label">Name:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['bride_name']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">Phone:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['bride_phone']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">Father's Name:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['bride_father']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">Mother's Name:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['bride_mother']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">Date of Birth:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['bride_birth']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">NID / Passport:</span>
                            <span class="cert-field-value">
                                <?php 
                                    $b_nid = sanitize($cert['bride_nid']);
                                    $b_pass = sanitize($cert['bride_passport']);
                                    echo !empty($b_nid) ? 'NID: ' . $b_nid : (!empty($b_pass) ? 'Passport: ' . $b_pass : 'N/A'); 
                                ?>
                            </span>
                        </div>
                        <div class="cert-field" style="grid-column: span 2;">
                            <span class="cert-field-label">Address:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['bride_address']); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- MARRIAGE PLACE & DATE -->
                <div class="cert-section">
                    <div class="section-divider">3. Marriage & Wali Details (বিবাহ ও অভিভাবক)</div>
                    <div class="cert-grid">
                        <div class="cert-field">
                            <span class="cert-field-label">Solemnization Date:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['marriage_date']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">Time of Solemn:</span>
                            <span class="cert-field-value"><?php echo date('h:i A', strtotime($cert['marriage_time'])); ?></span>
                        </div>
                        <div class="cert-field" style="grid-column: span 2;">
                            <span class="cert-field-label">Wali / Guardian:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['wali_name'] ?? 'N/A (No Representative)'); ?></span>
                        </div>
                        <div class="cert-field" style="grid-column: span 2;">
                            <span class="cert-field-label">Venue Place:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['marriage_place']); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- MAHR DETAILS -->
                <div class="cert-section">
                    <div class="section-divider">4. Mahr Details (মোহরানা)</div>
                    <div class="mahr-box">
                        <div class="mahr-grid">
                            <div>
                                <strong>Mahr Amount:</strong><br>
                                <?php echo number_format($cert['mahr_amount'], 2) . ' ' . sanitize($cert['currency']); ?>
                            </div>
                            <div>
                                <strong>Payment Type:</strong><br>
                                <?php 
                                    if ($cert['mahr_status'] === 'paid') echo 'Wasl (Paid)';
                                    elseif ($cert['mahr_status'] === 'due') echo "Mu'ajjal (Prompt/Due)";
                                    else echo 'Partially Paid';
                                ?>
                            </div>
                            <div>
                                <strong>Status:</strong><br>
                                <span style="text-transform: uppercase; font-weight: 700; color: var(--emerald-green);">
                                    <?php echo sanitize($cert['mahr_status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WITNESSES & REGISTRAR -->
                <div class="cert-section">
                    <div class="section-divider">5. Witnesses & Registrar Details (সাক্ষী ও কাজী)</div>
                    <div class="cert-grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 10px;">
                        <div class="cert-field">
                            <span class="cert-field-label">Witness 1 Name:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['witness1_name']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">Witness 1 NID:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['witness1_nid']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">Witness 2 Name:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['witness2_name']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">Witness 2 NID:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['witness2_nid']); ?></span>
                        </div>
                    </div>
                    <div class="cert-grid" style="border-top: 1px dotted #9CA3AF; padding-top: 8px;">
                        <div class="cert-field">
                            <span class="cert-field-label">Registrar Name:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['registrar_name']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">License Number:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['registrar_license']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">Phone:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['registrar_phone']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">Registrar Office:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['registrar_address']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- SPECIAL NOTES -->
                <?php if (!empty($cert['notes'])): ?>
                <div class="cert-section" style="margin-bottom: 25px;">
                    <div class="section-divider">6. Special Conditions / Notes (বিশেষ শর্তাবলী)</div>
                    <div style="font-size: 0.8rem; font-style: italic; line-height: 1.4; color: #4B5563;">
                        <?php echo nl2br(sanitize($cert['notes'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- SIGNATURE SECTION -->
                <div class="signature-section">
                    <div class="signature-grid">
                        <div class="sig-col">
                            <div class="sig-line"></div>
                            Signature of Bridegroom
                        </div>
                        <div class="sig-col">
                            <div class="sig-line"></div>
                            Signature of Bride
                        </div>
                        <div class="sig-col">
                            <div class="sig-line"></div>
                            Signature of Wali
                        </div>
                        <div class="sig-col">
                            <div class="sig-line"></div>
                            Witness Signatures
                        </div>
                        <div class="sig-col">
                            <div class="sig-line"></div>
                            Signature & Seal of Kazi
                        </div>
                    </div>
                </div>
                
                <!-- FOOTER WITH QR & VERIFY INFO -->
                <div class="cert-footer">
                    <div class="cert-qr">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=80&data=<?php echo urlencode($cert['qr_code']); ?>" alt="QR Code">
                    </div>
                    <div class="cert-footer-text">
                        Scan QR Code to verify legitimacy online or visit<br>
                        <strong><a href="<?php echo sanitize($cert['qr_code']); ?>" target="_blank"><?php echo sanitize($cert['qr_code']); ?></a></strong><br>
                        System Version 2.0 | Digital Marriage Registry | Generated Date: <?php echo date('Y-m-d H:i'); ?>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Auto Print Script -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Give layout a brief moment to render, then open browser print-to-pdf dialog
            setTimeout(() => {
                window.print();
                // Return back after printing finishes or cancels
                setTimeout(() => {
                    window.history.back();
                }, 500);
            }, 500);
        });
    </script>
</body>
</html>
