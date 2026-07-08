# Design Aesthetics & Typography Specification

The **Nikahnama Management System v2.0** uses high-end, premium design aesthetics to create a state-of-the-art visual experience. The layout is optimized for dual use: dynamic dark/light dashboard controls on screen and high-resolution, vector-crisp Islamic certificate layouts on paper.

---

## 1. Color Palette

### Digital Interface (Dashboard & Officer Panels)
- **Primary Accent:** `#FF8A00` (Spotify-inspired orange, representing vibrant clarity and modern engagement).
- **Primary Hover Accent:** `#E07A00` (Slightly darker shade for interactive hover feedback).
- **Background Light Theme:** `#F8F8F8` (Clean, off-white background that reduces eye strain compared to pure white).
- **Dark Navigation Background:** `#121212` (A rich, deep charcoal tone that provides high contrast against the accent orange).
- **Neutral Cards:** `#FFFFFF` (White cards with soft `#000000`/`0.04` shadows to build elevation and structure).

### Printable Document (Certificate Border & Elements)
- **Gold Border/Accents:** `#C5A059` / `#D4AF37` (A muted gold tone that prints beautifully without appearing overly brassy on white paper).
- **Emerald Green:** `#07472A` / `#0F5132` (An iconic, deep green color representing traditional Islamic design and government status).
- **Text Color:** `#1A1A1A` (Deep charcoal, ensuring absolute readability and high-resolution print margins).

---

## 2. Typography

The design imports three distinct font families from Google Fonts to serve different visual roles:
1. **Amiri (Serif):** Used primarily for the Arabic Bismillah script ("بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ") and Bengali main heading ("নিকাহনামা"). This font features clean, traditional Arabic characters.
2. **Montserrat (Sans-Serif):** Used for large headings, page headers, buttons, and system titles. Its geometric, bold structure gives a premium, premium feel.
3. **Inter (Sans-Serif):** Used for database field tables, inputs, data values, and descriptions. It is highly readable at small font sizes (0.85rem - 0.95rem) and optimizes column alignment.

---

## 3. Printable Certificate Structure (A4 portrait)

The layout of `print.php` is styled to fit on a single, standardized A4 sheet.

### Page Box Model
- **Dimensions:** 210mm x 297mm (Standard A4 portrait).
- **Page Margin:** 12mm (ensuring standard desktop printers do not clip borders).
- **Outer Border:** 4px double Gold (`#C5A059`) representing traditional certificate frames.
- **Inner Border:** 2px solid Emerald Green (`#07472A`) offset by a 5px gap.

### Key Layout Grid
- The page elements utilize a clean two-column grid structure (`grid-template-columns: 1fr 1fr`) to display Groom and Bride profiles side by side.
- Field rows use dotted borders (`border-bottom: 1px dotted #D1D5DB`) to give a traditional registry feel and guide the eye across fields.
- **Background Watermark:** A centered, translucent (opacity `0.055`) Islamic octagram star (vector SVG loaded inline) to prevent simple photocopy duplication.
- **QR Code Placeholder:** An 80px container at the bottom-left containing a dynamic QR code pointing to the public `verify.php` check. This allows physical paper validation using a mobile device scanner.
- **Signature Matrix:** 5 columns spanning the width of the certificate, providing official signing areas for the Groom, Bride, Wali, Witnesses, and Kazi (Registrar).
