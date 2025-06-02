<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Relatório de Clientes</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #fbbf24;
            padding-bottom: 10px;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background-color: #fbbf24;
            color: #78350f;
            text-align: left;
            padding: 8px;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .stats-container {
            display: inline;
            margin-bottom: 20px;
        }

        .stat-box {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            min-width: 200px;
            text-align: center;
            margin-bottom: 10px
        }

        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #b45309;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1 class="title">Relatório de Clientes</h1>
        <p class="subtitle">Flávio e Diana Perfumaria</p>
        <div class="period">
            Gerado em {{ now()->format('d/m/y \á\s H:i') }}
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="stats-container">
        <div class="stat-box">
            <div>Total de Clientes</div>
            <div class="stat-value">{{ $stats['total_customers'] }}</div>
        </div>
        <div class="stat-box">
            <div>Clientes Ativos</div>
            <div class="stat-value">{{ $stats['active_customers'] }}</div>
        </div>
        <div class="stat-box">
            <div>Maior Comprador</div>
            <div class="stat-value">
                {{ $stats['top_spender'] ? $stats['top_spender']->name : 'N/A' }}
            </div>
        </div>
        <div class="stat-box">
            <div>Cliente Mais Frequente</div>
            <div class="stat-value">
                {{ $stats['most_frequent'] ? $stats['most_frequent']->name : 'N/A' }}
            </div>
        </div>
    </div>

    <!-- Tabela de Clientes -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>Contato</th>
                <th>Total Compras</th>
                <th>Valor Total</th>
                <th>Última Compra</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $customer)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $customer->name }}</td>
                    <td>
                        {{ $customer->phone }}<br>
                        {{ $customer->email }}
                    </td>
                    <td>{{ $customer->sales->count() }}</td>
                    <td>R$ {{ number_format($customer->sales->sum('total'), 2, ',', '.') }}</td>
                    <td>
                        @if ($customer->sales->isNotEmpty())
                            {{ $customer->sales->sortByDesc('created_at')->first()->created_at->format('d/m/Y') }}
                        @else
                            Nunca comprou
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Relatório gerado em {{ now()->format('d/m/y \á\s h:i') }} | Flávio e Diana Perfumaria
        Relatório gerado automaticamente pelo sistema - {{ config('app.name') }}
    </div>
</body>

</html>
