<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Relatório de Vendas - {{ $period['start'] }} a {{ $period['end'] }}</title>
    <style type="text/css">
        /* Reset e Estilos Base */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            color: #333;
            background-color: white;
            padding: 25px;
            line-height: 1.5;
            font-size: 14px;
        }

        /* Cabeçalho */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #fbbf24;
            padding-bottom: 20px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #78350f;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 14px;
            color: #666;
            margin: 3px 0;
        }

        .period {
            background-color: #fef3c7;
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            margin-top: 10px;
            font-weight: 600;
            color: #92400e;
            font-size: 13px;
        }

        /* Seções */
        .section {
            margin-bottom: 35px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #78350f;
            border-left: 5px solid #fbbf24;
            padding-left: 10px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        /* Cards de Métricas */
        .metrics-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin: 20px 0;
            gap: 15px;
        }

        .metric-card {
            flex: 1;
            min-width: 200px;
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            margin-bottom: 10px
        }

        .metric-title {
            font-size: 14px;
            color: #92400e;
            margin-bottom: 5px;
        }

        .metric-value {
            font-size: 22px;
            font-weight: bold;
            color: #78350f;
        }

        /* Tabelas */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 13px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .table th {
            background-color: #78350f;
            color: white;
            text-align: left;
            padding: 10px 12px;
            font-weight: 600;
            font-size: 13px;
        }

        .table td {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }

        .table tr:nth-child(even) {
            background-color: #fefce8;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Destaques e Análises */
        .highlight {
            background-color: #fef3c7;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
        }

        .highlight-title {
            font-weight: 600;
            color: #78350f;
            margin-bottom: 10px;
            font-size: 15px;
        }

        .highlight-item {
            margin-bottom: 8px;
            font-size: 13px;
        }

        /* Rodapé */
        .footer {
            text-align: center;
            font-size: 12px;
            color: #999;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        /* Elementos de Destaque */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            background-color: #fbbf24;
            color: #78350f;
        }

        /* Responsividade para Impressão/PDF */
        @media print {
            body {
                padding: 10px;
                font-size: 12px;
            }

            .metric-card {
                page-break-inside: avoid;
                min-width: 150px;
            }

            .table {
                font-size: 11px;
            }

            .table th,
            .table td {
                padding: 6px 8px;
            }
        }
    </style>
</head>

<body>

    <div class="header">
        <h1 class="title">Relatório de Performance de Vendas</h1>
        <p class="subtitle"><strong>Flávio e Diana Perfumaria</strong></p>
        <div class="period">
            Período analisado: {{ $period['start'] }} a {{ $period['end'] }}
        </div>
    </div>

    <!-- Métricas Principais -->
    <div class="section">
        <h2 class="section-title">Visão Geral</h2>
        <div class="metrics-container">
            <div class="metric-card">
                <div class="metric-title">Faturamento Total</div>
                <div class="metric-value">R$ {{ number_format($summary['total_sales'], 2, ',', '.') }}</div>
            </div>

            <div class="metric-card">
                <div class="metric-title">Margem de Lucro Total</div>
                <div class="metric-value">R$ {{ number_format($summary['profit_margin'], 2, ',', '.') }}</div>
            </div>

            <div class="metric-card">
                <div class="metric-title">Total de Pedidos</div>
                <div class="metric-value">{{ $summary['total_orders'] }}</div>
            </div>

            <div class="metric-card">
                <div class="metric-title">Produtos Vendidos</div>
                <div class="metric-value">{{ $summary['total_products'] }}</div>
            </div>

            <div class="metric-card">
                <div class="metric-title">Ticket Médio</div>
                <div class="metric-value">R$ {{ number_format($analysis['avg_ticket'], 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- Destaques do Mês -->
    <div class="section">
        <h2 class="section-title">Destaques</h2>
        <div class="highlight">
            <div class="highlight-title">Performance do Mês</div>
            <div class="highlight-item">
                <strong>Produto mais vendido:</strong> {{ $summary['best_selling_product'] }}
            </div>
            <div class="highlight-item">
                <strong>Marca líder:</strong> {{ $summary['best_selling_brand'] }}
            </div>
            <div class="highlight-item">
                <strong>Dia de pico:</strong> {{ $summary['highest_revenue_day']['date'] }}
                (R$ {{ number_format($summary['highest_revenue_day']['revenue'], 2, ',', '.') }})
            </div>
        </div>
    </div>

    <!-- Vendas por Dia -->
    <div class="section">
        <h2 class="section-title">Vendas Diárias</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th class="text-center">Pedidos</th>
                    <th class="text-right">Faturamento</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($salesByDay as $day)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($day->sale_date)->format('d/m/Y') }}</td>
                        <td class="text-center">{{ $day->total_orders }}</td>
                        <td class="text-right">R$ {{ number_format($day->total_value, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Produtos -->
    <div class="section">
        <h2 class="section-title">Desempenho por Produto</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th class="text-center">Qtd Vendida</th>
                    <th class="text-right">Valor Total</th>
                    <th class="text-right">% do Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groupedProducts as $product)
                    <tr>
                        <td>{{ $product['name'] }}</td>
                        <td class="text-center">{{ $product['total_quantity'] }}</td>
                        <td class="text-right">R$ {{ number_format($product['total_value'], 2, ',', '.') }}</td>
                        <td class="text-right">
                            {{ number_format(($product['total_value'] / $summary['total_sales']) * 100, 2) }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Marcas -->
    <div class="section">
        <h2 class="section-title">Desempenho por Marca</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Marca</th>
                    <th class="text-center">Qtd Vendida</th>
                    <th class="text-right">Valor Total</th>
                    <th class="text-right">% do Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groupedByBrand as $brand)
                    <tr>
                        <td>{{ $brand->name }}</td>
                        <td class="text-center">{{ $brand->total_quantity }}</td>
                        <td class="text-right">R$ {{ number_format($brand->total_value, 2, ',', '.') }}</td>
                        <td class="text-right">
                            {{ number_format(($brand->total_value / $summary['total_sales']) * 100, 2) }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Análise -->
    <div class="section">
        <h2 class="section-title">Análise e Observações</h2>
        <div class="highlight">
            <div class="highlight-item">
                <strong>Média diária de vendas:</strong>
                R$ {{ number_format($analysis['daily_avg'], 2, ',', '.') }}
            </div>
            <div class="highlight-item">
                <strong>Dias sem vendas:</strong>
                {{ number_format($analysis['days_without_sales']) }} de {{ number_format($analysis['days_in_period']), 2 }} dias
            </div>
            <div class="highlight-item">
                <strong>Eficiência de vendas:</strong>
                {{ number_format($analysis['sales_efficiency'], 1) }}% dos dias com vendas
            </div>
            <div class="highlight-item">
                <strong>Produto com maior giro:</strong>
                {{ $analysis['top_product']['name'] ?? 'N/A' }}
                ({{ $analysis['top_product']['total_quantity'] ?? 0 }} unidades)
            </div>
        </div>
    </div>

    <div class="footer">
        Relatório gerado em {{ $report_date }} | Flávio e Diana Perfumaria
    </div>
</body>

</html>
