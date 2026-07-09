<?php
// print.php

require_once 'app/controllers/NikahController.php';
require_once 'app/helpers/session.php';

// Require authenticated session to prevent unauthorized access/scraping
require_login();

$controller = new NikahController();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("No certificate ID provided.");
}

$id = sanitize($_GET['id']);
$cert = $controller->show($id); // This will redirect if not found
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নিকাহনামা প্রিন্ট - <?php echo sanitize($cert['certificate_no']); ?></title>
    <!-- Custom A4 Print Style sheets -->
    <link rel="stylesheet" href="assets/css/print.css">
    <!-- FontAwesome for Print Control Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Screen controls styling */
        .print-control-bar {
            background-color: #343a40;
            padding: 12px;
            text-align: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .btn-print {
            background-color: #FF8A00;
            color: white;
            border: none;
            padding: 8px 24px;
            font-weight: 700;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-family: Arial, sans-serif;
            font-size: 0.95rem;
            margin-right: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: background-color 0.2s;
        }
        .btn-print:hover {
            background-color: #E07A00;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 18px;
            font-weight: 700;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-family: Arial, sans-serif;
            font-size: 0.95rem;
            transition: background-color 0.2s;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
        
        /* Adjust body for preview mode toolbar height */
        @media screen {
            body {
                padding-top: 70px;
                background-color: #525659;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Top Control Bar (Screen only) -->
    <div class="print-control-bar no-print">
        <button class="btn-print" onclick="window.print();">
            <i class="fa-solid fa-print me-2"></i>সার্টিফিকেট প্রিন্ট করুন (A4)
        </button>
        <a href="view.php?id=<?php echo $cert['id']; ?>" class="btn-back">
            <i class="fa-solid fa-arrow-left me-2"></i>বিবরণে ফিরে যান
        </a>
    </div>

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
                    <div class="govt-title">গণপ্রজাতন্ত্রী বাংলাদেশ সরকার</div>
                    <div class="cert-title-bn">নিকাহনামা</div>
                    <div class="cert-title-en">ডিজিটাল বিবাহ নিবন্ধন প্রমাণপত্র</div>
                </div>
                
                <!-- Certificate Metas -->
                <div class="cert-meta-row">
                    <div class="cert-meta-item">
                        সার্টিফিকেট নং: <span><?php echo sanitize($cert['certificate_no']); ?></span>
                    </div>
                    <div class="cert-meta-item">
                        নিবন্ধন নং: <span><?php echo sanitize($cert['registration_no']); ?></span>
                    </div>
                    <div class="cert-meta-item">
                        প্রদানের তারিখ: <span><?php echo date('d-m-Y'); ?></span>
                    </div>
                </div>
                
                <!-- GROOM SECTION -->
                <div class="cert-section">
                    <div class="section-divider">১. বরের বিবরণ</div>
                    <div class="cert-grid">
                        <div class="cert-field">
                            <span class="cert-field-label">বরের নাম:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['groom_name']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">মোবাইল নম্বর:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['groom_phone']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">পিতার নাম:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['groom_father']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">মাতার নাম:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['groom_mother']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">জন্ম তারিখ:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['groom_birth']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">NID / পাসপোর্ট:</span>
                            <span class="cert-field-value">
                                <?php 
                                    $g_nid = sanitize($cert['groom_nid']);
                                    $g_pass = sanitize($cert['groom_passport']);
                                    echo !empty($g_nid) ? 'NID: ' . $g_nid : (!empty($g_pass) ? 'পাসপোর্ট: ' . $g_pass : 'N/A'); 
                                ?>
                            </span>
                        </div>
                        <div class="cert-field" style="grid-column: span 2;">
                            <span class="cert-field-label">পূর্ণ ঠিকানা:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['groom_address']); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- BRIDE SECTION -->
                <div class="cert-section">
                    <div class="section-divider">২. কনের বিবরণ</div>
                    <div class="cert-grid">
                        <div class="cert-field">
                            <span class="cert-field-label">কনের নাম:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['bride_name']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">মোবাইল নম্বর:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['bride_phone']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">পিতার নাম:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['bride_father']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">মাতার নাম:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['bride_mother']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">জন্ম তারিখ:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['bride_birth']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">NID / পাসপোর্ট:</span>
                            <span class="cert-field-value">
                                <?php 
                                    $b_nid = sanitize($cert['bride_nid']);
                                    $b_pass = sanitize($cert['bride_passport']);
                                    echo !empty($b_nid) ? 'NID: ' . $b_nid : (!empty($b_pass) ? 'পাসপোর্ট: ' . $b_pass : 'N/A'); 
                                ?>
                            </span>
                        </div>
                        <div class="cert-field" style="grid-column: span 2;">
                            <span class="cert-field-label">পূর্ণ ঠিকানা:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['bride_address']); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- MARRIAGE PLACE & DATE -->
                <div class="cert-section">
                    <div class="section-divider">৩. বিবাহ ও অভিভাবক বিবরণ</div>
                    <div class="cert-grid">
                        <div class="cert-field">
                            <span class="cert-field-label">সম্পন্ন হওয়ার তারিখ:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['marriage_date']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">বিবাহের সময়:</span>
                            <span class="cert-field-value"><?php echo date('h:i A', strtotime($cert['marriage_time'])); ?></span>
                        </div>
                        <div class="cert-field" style="grid-column: span 2;">
                            <span class="cert-field-label">অভিভাবক / ওয়ালী:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['wali_name'] ?? 'অভিভাবক নেই'); ?></span>
                        </div>
                        <div class="cert-field" style="grid-column: span 2;">
                            <span class="cert-field-label">বিবাহের স্থান:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['marriage_place']); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- MAHR DETAILS -->
                <div class="cert-section">
                    <div class="section-divider">৪. দেনমোহর বিবরণ</div>
                    <div class="mahr-box">
                        <div class="mahr-grid">
                            <div>
                                <strong>দেনমোহরের পরিমাণ:</strong><br>
                                <?php echo number_format(floatval($cert['mahr_amount']), 2) . ' ' . sanitize($cert['currency']); ?>
                            </div>
                            <div>
                                <strong>পরিশোধের ধরন:</strong><br>
                                <?php 
                                    if ($cert['mahr_status'] === 'paid') echo 'পরিশোধিত (উসুল)';
                                    elseif ($cert['mahr_status'] === 'due') echo "বকেয়া (মুয়াজ্জাল)";
                                    else echo 'আংশিক পরিশোধিত';
                                ?>
                            </div>
                            <div>
                                <strong>অবস্থা:</strong><br>
                                <span style="text-transform: uppercase; font-weight: 700; color: var(--emerald-green);">
                                    <?php 
                                        if ($cert['mahr_status'] === 'paid') echo 'পরিশোধিত';
                                        elseif ($cert['mahr_status'] === 'due') echo "বকেয়া";
                                        else echo 'আংশিক';
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WITNESSES & REGISTRAR -->
                <div class="cert-section">
                    <div class="section-divider">৫. সাক্ষী ও কাজী বিবরণ</div>
                    <div class="cert-grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 10px;">
                        <div class="cert-field">
                            <span class="cert-field-label">১ম সাক্ষীর নাম:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['witness1_name']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">১ম সাক্ষীর NID:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['witness1_nid']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">২য় সাক্ষীর নাম:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['witness2_name']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">২য় সাক্ষীর NID:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['witness2_nid']); ?></span>
                        </div>
                    </div>
                    <div class="cert-grid" style="border-top: 1px dotted #9CA3AF; padding-top: 8px;">
                        <div class="cert-field">
                            <span class="cert-field-label">কাজী নাম:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['registrar_name']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">লাইসেন্স নম্বর:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['registrar_license']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">মোবাইল নম্বর:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['registrar_phone']); ?></span>
                        </div>
                        <div class="cert-field">
                            <span class="cert-field-label">কাজী কার্যালয়:</span>
                            <span class="cert-field-value"><?php echo sanitize($cert['registrar_address']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- SPECIAL NOTES -->
                <?php if (!empty($cert['notes'])): ?>
                <div class="cert-section" style="margin-bottom: 25px;">
                    <div class="section-divider">৬. বিশেষ শর্তাবলী ও মন্তব্য</div>
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
                            বরের স্বাক্ষর
                        </div>
                        <div class="sig-col">
                            <div class="sig-line"></div>
                            কনের স্বাক্ষর
                        </div>
                        <div class="sig-col">
                            <div class="sig-line"></div>
                            অভিভাবক/ওয়ালীর স্বাক্ষর
                        </div>
                        <div class="sig-col">
                            <div class="sig-line"></div>
                            সাক্ষীগণের স্বাক্ষর
                        </div>
                        <div class="sig-col">
                            <div class="sig-line"></div>
                            কাজী ও কার্যালয়ের সীল
                        </div>
                    </div>
                </div>
                
                <!-- FOOTER WITH QR & VERIFY INFO -->
                <div class="cert-footer">
                    <div class="cert-qr">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=80&data=<?php echo urlencode($cert['qr_code']); ?>" alt="QR Code">
                    </div>
                    <div class="cert-footer-text">
                        অনলাইনে সত্যতা যাচাই করতে কিউআর কোড স্ক্যান করুন অথবা ভিজিট করুন:<br>
                        <strong><a href="<?php echo sanitize($cert['qr_code']); ?>" target="_blank"><?php echo sanitize($cert['qr_code']); ?></a></strong><br>
                        ডিজিটাল নিকাহ রেজিস্ট্রি সিস্টেম | প্রস্তুতের তারিখ: <?php echo date('d-m-Y H:i'); ?>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

</body>
</html>
