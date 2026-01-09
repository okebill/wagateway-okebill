#!/bin/bash

# Script untuk menambahkan kontak secara bulk
# Solusi jangka panjang untuk menghindari LID error

echo "=========================================="
echo "  BULK ADD CONTACTS TO WHATSAPP"
echo "=========================================="
echo ""

# Warna
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${YELLOW}⚠ CATATAN PENTING:${NC}"
echo "Script ini akan membuat file VCF (vCard) yang bisa di-import"
echo "ke kontak WhatsApp untuk menghindari LID error."
echo ""
echo "WhatsApp Web API memerlukan nomor ada di kontak agar bisa"
echo "mengirim pesan tanpa LID error."
echo ""

# Check if contacts file exists
CONTACTS_FILE="customer-contacts.txt"

if [ ! -f "$CONTACTS_FILE" ]; then
    echo -e "${BLUE}File kontak tidak ditemukan. Membuat template...${NC}"
    echo ""
    
    cat > "$CONTACTS_FILE" << 'EOF'
# Format: NAMA|NOMOR_TELEPON
# Contoh:
# Budi Santoso|081234567890
# Siti Aminah|6281298765432
# Customer A|628988409756

# Tambahkan kontak pelanggan billing WiFi di bawah ini:
# Satu baris per kontak

EOF
    
    echo -e "${GREEN}✓${NC} Template file dibuat: $CONTACTS_FILE"
    echo ""
    echo "Langkah selanjutnya:"
    echo "1. Edit file $CONTACTS_FILE"
    echo "2. Tambahkan kontak pelanggan (format: NAMA|NOMOR)"
    echo "3. Jalankan script ini lagi"
    echo ""
    echo "Atau export dari database billing WiFi:"
    echo "   mysql -u user -p billing_db -e \"SELECT nama, no_hp FROM pelanggan WHERE status='aktif'\" | sed 's/\t/|/g' >> $CONTACTS_FILE"
    echo ""
    exit 0
fi

# Read and process contacts
echo -e "${BLUE}Membaca file kontak...${NC}"
CONTACT_COUNT=$(grep -v "^#" "$CONTACTS_FILE" | grep -v "^$" | wc -l)

if [ "$CONTACT_COUNT" -eq 0 ]; then
    echo -e "${RED}✗${NC} Tidak ada kontak ditemukan dalam file!"
    echo "Edit file $CONTACTS_FILE dan tambahkan kontak."
    exit 1
fi

echo -e "${GREEN}✓${NC} Ditemukan $CONTACT_COUNT kontak"
echo ""

# Generate VCF file
VCF_FILE="whatsapp-contacts-$(date +%Y%m%d-%H%M%S).vcf"
echo -e "${BLUE}Generating VCF file: $VCF_FILE${NC}"

# Clear VCF file
> "$VCF_FILE"

# Process each contact
COUNT=0
while IFS='|' read -r NAME PHONE; do
    # Skip comments and empty lines
    [[ "$NAME" =~ ^#.*$ ]] && continue
    [[ -z "$NAME" ]] && continue
    
    # Clean up phone number (remove spaces, dashes, etc)
    PHONE=$(echo "$PHONE" | tr -d ' -()' | sed 's/^0/62/')
    
    # Add to VCF
    cat >> "$VCF_FILE" << EOF
BEGIN:VCARD
VERSION:3.0
FN:$NAME
TEL;TYPE=CELL:+$PHONE
END:VCARD
EOF
    
    COUNT=$((COUNT + 1))
    echo "  [$COUNT/$CONTACT_COUNT] $NAME - $PHONE"
    
done < <(grep -v "^#" "$CONTACTS_FILE" | grep -v "^$")

echo ""
echo -e "${GREEN}✓${NC} VCF file berhasil dibuat: $VCF_FILE"
echo ""

# Instructions
echo "=========================================="
echo -e "${GREEN}  CARA IMPORT KE WHATSAPP${NC}"
echo "=========================================="
echo ""
echo "Metode 1: Import ke Kontak HP (RECOMMENDED)"
echo "--------------------------------------"
echo "1. Transfer file $VCF_FILE ke HP"
echo "2. Buka file VCF di HP (akan otomatis import ke Kontak)"
echo "3. WhatsApp akan otomatis sync kontak baru"
echo "4. Tunggu 5-10 menit untuk sinkronisasi"
echo ""

echo "Metode 2: Import ke Google Contacts"
echo "--------------------------------------"
echo "1. Buka https://contacts.google.com"
echo "2. Klik 'Import' → Pilih file $VCF_FILE"
echo "3. WhatsApp akan sync dari Google Contacts"
echo "4. Tunggu sinkronisasi selesai"
echo ""

echo "Metode 3: Import Manual"
echo "--------------------------------------"
echo "1. Buka WhatsApp di HP"
echo "2. Menu → Settings → Contacts → Import contacts"
echo "3. Pilih file $VCF_FILE"
echo ""

echo "=========================================="
echo -e "${YELLOW}⚠ PENTING:${NC}"
echo "Setelah import kontak, tunggu 10-15 menit agar"
echo "WhatsApp Web melakukan sinkronisasi penuh."
echo ""
echo "Verify sync dengan:"
echo "  curl -s http://localhost:3001/api/device/wa_bv4VOSGr3pEcX5mzCbfHbitfqQngsSEK/contacts | jq '.count'"
echo ""
echo "Setelah kontak tersync, LID error akan hilang!"
echo "=========================================="
echo ""

# Show file location
echo "File tersimpan di:"
echo "  $(pwd)/$VCF_FILE"
echo ""

# Optional: Create summary
echo "Summary:"
echo "  Total kontak: $COUNT"
echo "  File VCF: $VCF_FILE"
echo "  File sumber: $CONTACTS_FILE"
echo ""

# Bonus: Create SQL export template
if [ ! -f "export-contacts-from-db.sql" ]; then
    cat > "export-contacts-from-db.sql" << 'EOF'
-- Template SQL untuk export kontak dari database billing WiFi
-- Edit query sesuai struktur database Anda

-- Contoh untuk Mikrotik/RADIUS billing:
SELECT 
    CONCAT(firstname, ' ', lastname) as nama,
    phone as nomor
FROM radcheck 
WHERE phone IS NOT NULL AND phone != ''
INTO OUTFILE '/tmp/contacts.csv'
FIELDS TERMINATED BY '|'
LINES TERMINATED BY '\n';

-- Atau untuk database custom:
SELECT 
    nama_pelanggan as nama,
    no_hp as nomor
FROM pelanggan 
WHERE status = 'aktif' AND no_hp IS NOT NULL
INTO OUTFILE '/tmp/contacts.csv'
FIELDS TERMINATED BY '|'
LINES TERMINATED BY '\n';

-- Setelah export, copy ke customer-contacts.txt:
-- cat /tmp/contacts.csv >> customer-contacts.txt
EOF
    
    echo -e "${BLUE}ℹ${NC} Template SQL dibuat: export-contacts-from-db.sql"
    echo "   Edit file ini untuk export dari database billing"
    echo ""
fi

