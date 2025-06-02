<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Catálogo Flávio e Diana Perfumaria</title>
    <style type="text/css">
        /* Reset e Estilos Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            background-color: #f5f1ea;
            padding: 0;
            line-height: 1.4;
            font-size: 12px;
            page-break-inside: avoid
        }

        /* Cabeçalho */
        .header {
            background-color: #fbbf24;
            padding: 15px 20px;
            text-align: center;
            border-bottom: 3px solid #b45309;
        }

        .logo {
            color: #78350f;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #b45309;
            font-size: 12px;
            letter-spacing: 1px;
        }

        /* Container Principal */
        .catalog-container {
            padding: 0 15px;
            page-break-inside: avoid;
        }

        /* Seção de Marcas */
        .brand-section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .brand-header {
            background-color: #f3f4f6;
            padding: 8px 12px;
            border-left: 4px solid #fbbf24;
            margin: 15px 0;
            font-size: 18px;
            font-weight: bold;
        }

        .brand-description {
            font-size: 13px;
            color: #555;
            margin: 5px 0 15px 0;
            line-height: 1.4;
            text-align: left;
            font-style: italic;
        }

        /* Grade de Produtos */
        .product-grid {
            width: 100%;
        }

        .product-item {
            display: inline-block;
            vertical-align: top;
            width: 19.2%;
            height: 250px;
            margin: 1%;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 5px;
            padding: 12px;
            text-align: center;
            height: 220px;
            page-break-inside: avoid;
        }

        .product-image-container {
            height: 130px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }

        .product-image {
            max-width: 100%;
            max-height: 130px;
            object-fit: contain;
            border-radius: 3px;
        }

        .product-name {
            font-size: 13px;
            color: #1f2937;
            margin: 5px 0;
            height: 35px;
            overflow: hidden;
        }

        .product-price {
            color: #92400e;
            font-weight: bold;
            font-size: 14px;
        }

        .footer {
            page-break-before: always;
        }

        .footer-content {
            background-color: #78350f;
            color: white;
            padding: 10px 0 0 0;
            text-align: center;
            height: 1112px;
        }

        .footer-title {
            align-self: center;
            font-size: 28px;
            margin-top: 400px;
        }

        .footer-contact {
            font-size: 16px;
            color: #FFFFFA;
            margin: 5px 0;
        }

        .divider {
            width: 80%;
            border-top: 1px solid #b45309;
            margin: 15px auto;
        }

        .copyright {
            font-size: 14px;
            color: white;
            margin-top: 10px;
        }

        /* Clearfix */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Melhorias para impressão */
        @media print {
            body {
                padding: 0;
                font-size: 11px;
            }

            .product-item {
                height: auto;
                min-height: 220px;
            }
        }
    </style>
</head>

<body>
    <header class="header">
        <div>
            <h1 class="logo">Flávio e Diana Perfumaria</h1>
            <p class="subtitle">PERFUMARIA • COSMÉTICOS • PRESENTES</p>
        </div>
    </header>

    <div class="catalog-container">
        @foreach ($brands as $brand)
            <div class="brand-section">
                <div class="brand-header">{{ $brand->name }}
                    @if ($brand->description)
                        <div class="brand-description">
                            {{ $brand->description }}
                        </div>
                    @endif
                </div>

                <div class="product-grid clearfix">
                    @foreach ($brand->products as $product)
                        @if ($product->show_on_catalog)
                            <div class="product-item">
                                <div class="product-image-container">
                                    @if ($product->image)
                                        <img src="{{ storage_path('app/public/' . $product->image) }}"
                                            class="product-image">
                                    @else
                                        <img src="{{ storage_path('app/public/products/product_placeholder.png') }}"
                                            class="product-image">
                                    @endif
                                </div>
                                <h3 class="product-name">{{ $product->name }}</h3>
                                <p class="product-price">R$ {{ number_format($product->sale_value, 2, ',', '.') }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <footer class="footer">
        <div class="footer-content">
            <h2 class="footer-title">Flávio e Diana Perfumaria</h2>
            <p class="footer-contact">Telefone: (51) 9 9146 9669</p>
            <p class="footer-contact">Email: rojane.ro766@gmail.com</p>
            <div class="divider"></div>
            <p class="copyright">© {{ date('Y') }} Flávio e Diana Perfumaria - Todos os direitos reservados.</p>
        </div>
    </footer>
</body>

</html>
