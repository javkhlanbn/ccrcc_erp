# TCPDF Installation Guide

PDF экспорт боломжтой болгохын тулд TCPDF санг суулгах шаажтай:

## Сууцны заавар:

1. TCPDF санг татаж авах:
   - https://github.com/tecnickcom/TCPDF/releases хаягаас хамгийн сүүлийн хувилбарыг татна
   - Эсвэл Composer ашиглан: `composer require tecnickcom/tcpdf`

2. Файлуудыг хуулах:
   - TCPDF-ийн бүх файлуудыг `app/tcpdf/` фолдерт хуулна
   - Фолдерын бүтэц: `app/tcpdf/tcpdf.php` байх ёстой

3. Шалгах:
   - task-reports.php хуудсанд орж PDF Export товчийг дарж үзнэ
   - Хэрэв TCPDF байхгүй бол Excel эсвэл Word format ашиглана

## Өөр сонголт:

Хэрэв TCPDF суулгахад бэрхшээл байвал Excel болон Word export ашиглаж болно.
Эдгээр нь браузер дээр шууд ажиллах тул нэмэлт сан шаардлагагүй.

## Файлын бүтэц:
```
app/
├── export-report.php
├── tcpdf/
│   ├── tcpdf.php
│   ├── config/
│   ├── fonts/
│   └── ...
└── ...
```
