<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Extrato de Vendas</title>
    <style type="text/css">
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

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #fbbf24;
            padding-bottom: 20px;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            color: #78350f;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #78350f;
            border-left: 5px solid #fbbf24;
            padding-left: 10px;
            margin-bottom: 10px;
        }

        .info-table td {
            padding: 4px 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th {
            background-color: #78350f;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 13px;
        }

        .table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }

        .table tr:nth-child(even) {
            background-color: #fefce8;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #999;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .sale-separator {
            border-top: 2px dashed #ccc;
            margin: 30px 0;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1 class="title">Extrato de Vendas: {{ $sales[0]->customer->name }}</h1>
        <p class="subtitle"><strong>Flávio e Diana Perfumaria</strong></p>
        <p class="subtitle">Emitido em {{ $reportDate }}</p>
    </div>

    <div class="section">
        <h2 class="section-title">Informações do Cliente</h2>
        <table class="info-table">
            <tr>
                <td><strong>Nome:</strong></td>
                <td>{{ $sales[0]->customer->name }}</td>
            </tr>
            <tr>
                <td><strong>CPF:</strong></td>
                <td>{{ $sales[0]->customer->cpf ?? '-' }}</td>
            </tr>

            <tr>
                <td><strong>Valor devido este mês:</strong></td>
                <td>{{ $debtThisMonth ?? '-' }}</td>
            </tr>
        </table>
    </div>



    @foreach ($sales as $sale)
        <div class="section">
            <h2 class="section-title">Venda #{{ $sale->id }}</h2>

            <h3 class="section-title">Parcelas</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Parcela</th>
                        <th>Valor</th>
                        <th>Vencimento</th>
                        <th>Pagamento</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->installments as $installment)
                        <tr>
                            <td>{{ $installment->installment_number }}</td>
                            <td>R$ {{ number_format($installment->amount, 2, ',', '.') }}</td>
                            <td>{{ \Carbon\Carbon::parse($installment->due_date)->format('d/m/Y') }}</td>
                            <td>
                                {{ $installment->payment_date ? \Carbon\Carbon::parse($installment->payment_date)->format('d/m/Y') : '-' }}
                            </td>
                            <td>{{ ucfirst($installment->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h3 class="section-title" style="margin-top: 20px;">Produtos</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Preço Unitário</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->products as $product)
                        <tr>
                            <td>{{ $product->product->name }}</td>
                            <td>{{ $product->quantity }}</td>
                            <td>R$ {{ number_format($product->product->sale_value, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($product->product->sale_value * $product->quantity, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h3 class="section-title" style="margin-top: 20px;">Totais</h3>
            <table class="info-table">
                @if ($sale->discount > 0)
                    <tr>
                        <td><strong>Total Bruto:</strong></td>
                        <td><strong>R$ {{ number_format($sale->raw_total, 2, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Desconto:</strong></td>
                        <td>R$ {{ number_format($sale->discount, 2, ',', '.') }}</td>
                    </tr>
                @endif
                <tr>
                    <td><strong>Total:</strong></td>
                    <td><strong>R$ {{ number_format($sale->total, 2, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td><strong>Pago até o momento:</strong></td>
                    <td>R$ {{ number_format($sale->paid, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Valor em aberto:</strong></td>
                    <td>R$ {{ number_format($sale->debt, 2, ',', '.') }}</td>
                </tr>
            </table>

            @if ($sale->description)
                <div class="section" style="margin-top: 10px;">
                    <h3 class="section-title">Observações</h3>
                    <p>{{ $sale->description }}</p>
                </div>
            @endif
        </div>

        @if (!$loop->last)
            <div class="sale-separator"></div>
        @endif
    @endforeach

    <div class="footer">
        Extrato gerado em {{ $reportDate }} | Flávio e Diana Perfumaria
    </div>
</body>

</html>
