<?php
// print_new_muslim.php

require_once 'app/controllers/NikahController.php';
require_once 'app/helpers/session.php';

// Guards
require_login();

$controller = new NikahController();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("No record ID provided.");
}

$id = sanitize($_GET['id']);
$cert = $controller->showNewMuslim($id); // Redirects if not found

$type = $_GET['type'] ?? 'certificate';
$valid_types = ['certificate', 'declaration', 'affidavit', 'witness'];
if (!in_array($type, $valid_types)) {
    $type = 'certificate';
}

// Age calculation helper
$dob = new DateTime($cert['date_of_birth']);
$now = new DateTime($cert['declaration_date']);
$age = $now->diff($dob)->y;
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php 
            if ($type === 'certificate') echo "ইসলাম গ্রহণের সনদ";
            elseif ($type === 'declaration') echo "ইসলাম গ্রহণের ঘোষণা";
            elseif ($type === 'affidavit') echo "ধর্ম পরিবর্তনের হলফনামা";
            else echo "সাক্ষীর বিবৃতি";
        ?> - <?php echo sanitize($cert['new_name']); ?>
    </title>
    <!-- Custom A4 Print Stylesheet -->
    <link rel="stylesheet" href="assets/css/print.css">
    <!-- FontAwesome for Icons -->
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
        
        @media screen {
            body {
                padding-top: 70px;
                background-color: #525659;
            }
        }

        /* Specific styles for legal/stamp paper look */
        .stamp-paper-header {
            border: 2px dashed #9CA3AF;
            padding: 25px;
            text-align: center;
            margin-bottom: 30px;
            color: #9CA3AF;
            text-transform: uppercase;
            font-weight: bold;
            font-size: 1.1rem;
            border-radius: 6px;
        }
        .statement-text {
            font-size: 0.95rem;
            line-height: 1.8;
            text-align: justify;
            color: #1F2937;
        }
        .legal-point {
            margin-bottom: 15px;
            padding-left: 25px;
            position: relative;
        }
        .legal-point::before {
            content: "•";
            position: absolute;
            left: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- Floating Top Control Bar (Screen only) -->
    <div class="print-control-bar no-print">
        <button class="btn-print" onclick="window.print();">
            <i class="fa-solid fa-print me-2"></i>ডকুমেন্ট প্রিন্ট করুন (A4)
        </button>
        <a href="view_new_muslim.php?id=<?php echo $cert['id']; ?>" class="btn-back">
            <i class="fa-solid fa-arrow-left me-2"></i>বিবরণে ফিরে যান
        </a>
    </div>

    <!-- Official Certificate Wrapper -->
    <div class="certificate-wrapper">
        <div class="certificate-border">
            <div class="certificate-inner-border">
                <div class="certificate-watermark"></div>

                <!-- 1. ISLAM CONVERSION CERTIFICATE -->
                <?php if ($type === 'certificate'): ?>
                    <div class="cert-header">
                        <div class="bismillah">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّহِيمِ</div>
                        <div class="govt-title">ডিজিটাল নওমুসলিম ডাটাবেজ ও রেজিস্ট্রি</div>
                        <div class="cert-title-bn">ইসলাম গ্রহণের সনদপত্র</div>
                        <div class="cert-title-en">CERTIFICATE OF EMBRACING ISLAM</div>
                    </div>

                    <div class="cert-meta-row">
                        <div class="cert-meta-item">সনদ নং: <span><?php echo sanitize($cert['certificate_no']); ?></span></div>
                        <div class="cert-meta-item">গ্রহণের তারিখ: <span><?php echo sanitize($cert['declaration_date']); ?></span></div>
                        <div class="cert-meta-item">ইস্যুর তারিখ: <span><?php echo date('d-m-Y'); ?></span></div>
                    </div>

                    <div class="cert-section" style="margin-top: 30px;">
                        <div class="statement-text">
                            এই মর্মে প্রত্যয়ন করা যাইতেছে যে, 
                            পূর্বের নাম <strong><?php echo sanitize($cert['previous_name']); ?></strong>, 
                            ধর্ম: <strong><?php echo sanitize($cert['previous_religion']); ?></strong>, 
                            পিতা: <strong><?php echo sanitize($cert['father_name']); ?></strong>, 
                            মাতা: <strong><?php echo sanitize($cert['mother_name']); ?></strong>, 
                            ঠিকানা: <strong><?php echo sanitize($cert['address']); ?></strong>। 
                            তিনি অদ্য <strong><?php echo sanitize($cert['declaration_date']); ?></strong> তারিখে 
                            সম্পূর্ণ সজ্ঞানে, স্বেচ্ছায়, কারো প্ররোচনা বা জোরজবরদস্তি ছাড়াই পবিত্র ইসলাম ধর্মের প্রতি আকৃষ্ট হইয়া 
                            <strong><?php echo sanitize($cert['institution_name']); ?></strong>-এ উপস্থিত হইয়া 
                            সম্মানিত ইমাম <strong><?php echo sanitize($cert['imam_name']); ?></strong> এর সম্মুখে পবিত্র কালিমাহ শাহাদাত: 
                            <span class="d-block text-center fw-bold my-3 fs-5">"أَشْهَدُ أَنْ لَا إِلٰهَ إِلَّا اللهُ وَأَشْهَدُ أَنَّ مُحَمَّدًا عَبْدُهُ وَرَسُولُهُ"</span>
                            (আমি সাক্ষ্য দিচ্ছি যে, আল্লাহ ছাড়া কোনো মাবুদ নেই এবং আমি সাক্ষ্য দিচ্ছি যে, মুহাম্মদ আল্লাহর বান্দা ও রাসূল) 
                            পাঠ করিয়া পবিত্র ইসলাম ধর্ম গ্রহণ করিয়াছেন এবং নিজের নতুন ইসলামী নাম <strong><?php echo sanitize($cert['new_name']); ?></strong> ধারণ করিয়াছেন।
                        </div>
                    </div>

                    <div class="cert-section" style="margin-top: 40px;">
                        <div class="section-divider">নিবন্ধিত বিবরণ ও পরিচয়</div>
                        <div class="cert-grid">
                            <div class="cert-field"><span class="cert-field-label">নতুন নাম:</span><span class="cert-field-value"><?php echo sanitize($cert['new_name']); ?></span></div>
                            <div class="cert-field"><span class="cert-field-label">পূর্বের নাম:</span><span class="cert-field-value"><?php echo sanitize($cert['previous_name']); ?></span></div>
                            <div class="cert-field"><span class="cert-field-label">মোবাইল:</span><span class="cert-field-value"><?php echo sanitize($cert['phone_no']); ?></span></div>
                            <div class="cert-field"><span class="cert-field-label">জন্ম তারিখ:</span><span class="cert-field-value"><?php echo sanitize($cert['date_of_birth']); ?> (বয়স: <?php echo $age; ?> বছর)</span></div>
                            <div class="cert-field" style="grid-column: span 2;"><span class="cert-field-label">জাতীয় পরিচয়পত্র (NID):</span><span class="cert-field-value"><?php echo sanitize($cert['nid_no'] ?? 'N/A'); ?></span></div>
                        </div>
                    </div>

                    <div class="signature-section" style="margin-top: 80px;">
                        <div class="signature-grid" style="grid-template-columns: 1fr 1fr 1fr;">
                            <div class="sig-col"><div class="sig-line"></div>নওমুসলিমের স্বাক্ষর</div>
                            <div class="sig-col"><div class="sig-line"></div>দীক্ষাদানকারী ইমামের স্বাক্ষর</div>
                            <div class="sig-col"><div class="sig-line"></div>প্রতিষ্ঠানের সিল ও কাজী স্বাক্ষর</div>
                        </div>
                    </div>

                <!-- 2. DECLARATION OF EMBRACING ISLAM -->
                <?php elseif ($type === 'declaration'): ?>
                    <div class="cert-header">
                        <div class="bismillah">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّহِيمِ</div>
                        <div class="cert-title-bn">স্বেচ্ছায় ইসলাম ধর্ম গ্রহণের ঘোষণাপত্র</div>
                        <div class="cert-title-en">DECLARATION OF EMBRACING ISLAM</div>
                    </div>

                    <div class="cert-section" style="margin-top: 25px;">
                        <div class="statement-text">
                            আমি <strong><?php echo sanitize($cert['new_name']); ?></strong> (পূর্বের নাম: <strong><?php echo sanitize($cert['previous_name']); ?></strong>), 
                            পিতা: <strong><?php echo sanitize($cert['father_name']); ?></strong>, মাতা: <strong><?php echo sanitize($cert['mother_name']); ?></strong>, 
                            জন্ম তারিখ: <strong><?php echo sanitize($cert['date_of_birth']); ?></strong> (বয়স: <?php echo $age; ?> বছর), 
                            জাতীয় পরিচয়পত্র (NID): <strong><?php echo sanitize($cert['nid_no'] ?? 'N/A'); ?></strong>, 
                            ঠিকানা: <strong><?php echo sanitize($cert['address']); ?></strong>। পরম করুণাময় ও অসীম দয়ালু আল্লাহর নামে শপথপূর্বক নিম্নলিখিত ঘোষণা প্রদান করিতেছি:
                            
                            <div class="mt-3">
                                <div class="legal-point">আমি ঘোষণা করছি যে, আমি প্রাপ্তবয়স্ক এবং আমার নিজস্ব ভালো-মন্দ বিচার করার সম্পূর্ণ জ্ঞান রয়েছে।</div>
                                <div class="legal-point">আমি কোনো প্রকার প্রলোভন, ভীতিপ্রদর্শন, জোরজবরদস্তি বা কারো দ্বারা প্ররোচিত না হয়ে সম্পূর্ণরূপে নিজের মন থেকে সত্য ধর্ম হিসেবে অনুধাবন করে মহান আল্লাহর সন্তুষ্টি অর্জনের জন্য ইসলাম ধর্ম গ্রহণ করেছি।</div>
                                <div class="legal-point">আমি আন্তরিক বিশ্বাসের সহিত মহান আল্লাহর একত্ববাদকে স্বীকার করে সম্মানিত ইমামের সম্মুখে পবিত্র কালেমা শাহাদাত পাঠ করেছি।</div>
                                <div class="legal-point">আমি আজ থেকে আমার পূর্বের ধর্ম <strong><?php echo sanitize($cert['previous_religion']); ?></strong> পরিত্যাগ করে নিজেকে একজন মুসলিম হিসেবে ঘোষণা করছি এবং আজীবন ইসলামের বিধি-বিধান মেনে চলব।</div>
                                <div class="legal-point">আমি স্বেচ্ছায় আমার পূর্বের নাম পরিবর্তন করে নতুন ইসলামী নাম <strong><?php echo sanitize($cert['new_name']); ?></strong> গ্রহণ করলাম এবং এখন থেকে আমি এই নামেই সর্বত্র পরিচিত হব।</div>
                            </div>
                        </div>
                    </div>

                    <div class="signature-section" style="margin-top: 100px;">
                        <div class="signature-grid">
                            <div class="sig-col"><div class="sig-line"></div>ঘোষণাকারীর স্বাক্ষর (নতুন নাম)</div>
                            <div class="sig-col"><div class="sig-line"></div>ঘোষণাকারীর স্বাক্ষর (পূর্বের নাম)</div>
                            <div class="sig-col"><div class="sig-line"></div>১ম সাক্ষীর স্বাক্ষর</div>
                            <div class="sig-col"><div class="sig-line"></div>২য় সাক্ষীর স্বাক্ষর</div>
                            <div class="sig-col"><div class="sig-line"></div>ইমাম/কাজী স্বাক্ষর ও সীল</div>
                        </div>
                    </div>

                <!-- 3. COURT AFFIDAVIT TEMPLATE -->
                <?php elseif ($type === 'affidavit'): ?>
                    <div class="stamp-paper-header no-print">
                        [১০০ টাকার নন-জুডিশিয়াল স্ট্যাম্পের জন্য নির্ধারিত খালি জায়গা]
                    </div>

                    <div class="cert-header" style="margin-top: 10px;">
                        <div class="cert-title-bn">ধর্ম বিশ্বাস পরিবর্তন ও নাম সংশোধনের হলফনামা</div>
                        <div class="govt-title" style="font-size: 0.95rem;">(প্রথম শ্রেণীর ম্যাজিস্ট্রেট বা নোটারী পাবলিকের কার্যালয়, বাংলাদেশ)</div>
                    </div>

                    <div class="cert-section" style="margin-top: 30px;">
                        <div class="statement-text">
                            আমি <strong><?php echo sanitize($cert['new_name']); ?></strong>, পূর্বের নাম: <strong><?php echo sanitize($cert['previous_name']); ?></strong>, 
                            পিতা: <strong><?php echo sanitize($cert['father_name']); ?></strong>, মাতা: <strong><?php echo sanitize($cert['mother_name']); ?></strong>, 
                            ধর্ম: ইসলাম (পূর্বের ধর্ম: <?php echo sanitize($cert['previous_religion']); ?>), পেশা: চাকরি/ব্যবসা, 
                            জাতীয়তা: বাংলাদেশী (জন্মসূত্রে), ঠিকানা: <strong><?php echo sanitize($cert['address']); ?></strong>।
                            
                            <p class="mt-3">আমি শপথপূর্বক ও হলফনামা সহকারে অত্র আদালতে এই মর্মে ঘোষণা করিতেছি যে:</p>
                            
                            <div>
                                <div class="legal-point">আমি হলফকারী সুস্থ শরীরে ও সজ্ঞানে সুস্থ মস্তিষ্কে অত্র হলফনামা সম্পাদন করছি।</div>
                                <div class="legal-point">আমি ইতিপূর্বে সনাতন/অন্য ধর্মীয় রীতিনীতি মেনে চলতাম। কিন্তু আমি দীর্ঘদিন যাবৎ ইসলাম ধর্মের মহত্ত্ব, উদারতা ও শ্রেষ্ঠত্ব সম্পর্কে পড়াশোনা করে মনে-প্রাণে বিশ্বাস করি যে মহান আল্লাহ তাআলাই একমাত্র মাবুদ এবং হযরত মুহাম্মদ (সা:) আল্লাহর প্রেরিত শেষ রসূল।</div>
                                <div class="legal-point">তদানুযায়ী, আমি অদ্য <strong><?php echo sanitize($cert['declaration_date']); ?></strong> তারিখে <strong><?php echo sanitize($cert['institution_name']); ?></strong>-এর সম্মানিত ইমাম <strong><?php echo sanitize($cert['imam_name']); ?></strong> এর নিকট হাজির হয়ে স্বেচ্ছায় কালেমা শাহাদাত পাঠ করে পবিত্র ইসলাম ধর্ম গ্রহণ করেছি।</div>
                                <div class="legal-point">ইসলাম গ্রহণ করার পর আমি আমার পূর্বের নাম <strong><?php echo sanitize($cert['previous_name']); ?></strong> পরিবর্তন করে নতুন ইসলামী নাম <strong><?php echo sanitize($cert['new_name']); ?></strong> ধারণ করলাম।</div>
                                <div class="legal-point">এখন থেকে সরকারি, বেসরকারি ও সামাজিক সকল প্রকার নথিপত্রে ও লেনদেনে আমি আমার নতুন নাম <strong><?php echo sanitize($cert['new_name']); ?></strong> ব্যবহার করব।</div>
                                <div class="legal-point">অত্র হলফনামায় বর্ণিত সকল বক্তব্য আমার জ্ঞান ও বিশ্বাস মতে সম্পূর্ণ সত্য এবং এতে কোনো তথ্য গোপন করা হয়নি।</div>
                            </div>
                        </div>
                    </div>

                    <div class="signature-section" style="margin-top: 100px;">
                        <div class="signature-grid" style="grid-template-columns: 1fr 2fr;">
                            <div class="sig-col text-start">
                                <br><br>
                                সনাক্তকারী অ্যাডভোকেট স্বাক্ষর
                            </div>
                            <div class="sig-col text-end">
                                <div class="sig-line" style="margin-left: auto; width: 60%;"></div>
                                হলফকারীর স্বাক্ষর (নতুন নাম)<br>
                                এবং সীল (নোটারী পাবলিক / ম্যাজিস্ট্রেট)
                            </div>
                        </div>
                    </div>

                <!-- 4. WITNESS STATEMENT -->
                <?php else: ?>
                    <div class="cert-header">
                        <div class="bismillah">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّহِيمِ</div>
                        <div class="cert-title-bn">সাক্ষীগণের লিখিত জবানবন্দী ও বিবৃতি</div>
                        <div class="cert-title-en">WITNESS TESTIMONY STATEMENT</div>
                    </div>

                    <div class="cert-section" style="margin-top: 30px;">
                        <div class="statement-text">
                            আমরা নিম্নস্বাক্ষরকারী সাক্ষীবৃন্দ এই মর্মে লিখিত সাক্ষ্য প্রদান ও নিশ্চয়তা দিচ্ছি যে, 
                            আমরা ব্যক্তি হিসেবে <strong><?php echo sanitize($cert['new_name']); ?></strong> (পূর্বের নাম: <strong><?php echo sanitize($cert['previous_name']); ?></strong>)-কে ব্যক্তিগতভাবে চিনি ও জানি।
                            
                            <p class="mt-3">আমরা প্রত্যয়ন করছি যে, অদ্য <strong><?php echo sanitize($cert['declaration_date']); ?></strong> তারিখে তিনি আমাদের প্রত্যক্ষ উপস্থিতিতে সম্পূর্ণ সুস্থ মস্তিষ্কে ও স্বেচ্ছায় কোনো প্ররোচনা ছাড়াই পবিত্র ইসলাম ধর্ম গ্রহণ করেছেন। আমরা তাঁর কালেমা পাঠের প্রত্যক্ষ সাক্ষী।</p>
                            
                            <p>আমরা তাঁর সত্যনিষ্ঠা ও ইসলামের প্রতি আন্তরিক ভালোবাসার সত্যতা নিশ্চিত করছি এবং তাঁর নতুন নাম ও ধর্মীয় পরিচয় পরিবর্তনের সাক্ষী হিসেবে নিচে স্বাক্ষর প্রদান করছি।</p>
                        </div>
                    </div>

                    <!-- Witness details grid -->
                    <div class="cert-section" style="margin-top: 45px;">
                        <h5 class="fw-bold border-bottom pb-2 text-dark">সাক্ষীগণের বিবরণ ও দস্তখত</h5>
                        <div class="row" style="display: flex; gap: 30px; margin-top: 20px;">
                            <div style="flex: 1; border: 1px solid #E5E7EB; padding: 20px; border-radius: 6px;">
                                <strong>১ম সাক্ষী:</strong><br>
                                নাম: <?php echo sanitize($cert['witness1_name']); ?><br>
                                NID: <?php echo sanitize($cert['witness1_nid']); ?><br>
                                ঠিকানা: <?php echo sanitize($cert['witness1_address'] ?? 'N/A'); ?><br><br>
                                <div class="sig-line" style="margin-top: 30px; width: 100%;"></div>
                                স্বাক্ষর ও তারিখ
                            </div>
                            <div style="flex: 1; border: 1px solid #E5E7EB; padding: 20px; border-radius: 6px;">
                                <strong>২য় সাক্ষী:</strong><br>
                                নাম: <?php echo sanitize($cert['witness2_name']); ?><br>
                                NID: <?php echo sanitize($cert['witness2_nid']); ?><br>
                                ঠিকানা: <?php echo sanitize($cert['witness2_address'] ?? 'N/A'); ?><br><br>
                                <div class="sig-line" style="margin-top: 30px; width: 100%;"></div>
                                signature ও তারিখ
                            </div>
                        </div>
                    </div>

                    <div class="signature-section" style="margin-top: 80px;">
                        <div class="signature-grid" style="grid-template-columns: 1fr 1fr;">
                            <div class="sig-col"><div class="sig-line"></div>নওমুসলিমের স্বাক্ষর</div>
                            <div class="sig-col"><div class="sig-line"></div>দীক্ষাদানকারী ইমাম / কাজীর স্বাক্ষর ও সীল</div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- COMMON FOOTER WITH QR & VERIFICATION INFO -->
                <div class="cert-footer" style="margin-top: 60px;">
                    <div class="cert-qr">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=70&data=<?php echo urlencode($cert['qr_code']); ?>" alt="QR Code">
                    </div>
                    <div class="cert-footer-text">
                        অনলাইনে সত্যতা যাচাই করতে কিউআর কোড স্ক্যান করুন অথবা ভিজিট করুন:<br>
                        <strong><a href="<?php echo sanitize($cert['qr_code']); ?>" target="_blank"><?php echo sanitize($cert['qr_code']); ?></a></strong><br>
                        নওমুসলিম ডিজিটাল রেজিস্ট্রি মডিউল | সিস্টেম ভার্সন ২.০ | তৈরির তারিখ: <?php echo date('d-m-Y H:i'); ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>
