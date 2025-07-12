<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Recibo de Apartado</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .folio {
            text-align: right;
            font-weight: bold;
        }

        .content {
            margin-top: 40px;
        }

        .property-info {
            border: 1px solid #ccc;
            padding: 15px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>RECIBO DE APARTADO</h1>
    </div>
    <div class="folio">
        Folio: <?php echo htmlspecialchars($folio); ?> <br>
        Fecha: <?php echo date('d/m/Y'); ?>
    </div>

    <div class="content">
        <p>Recibimos de <strong><?php echo htmlspecialchars($prospecto_nombre); ?></strong> la cantidad de <strong>$$10,000.00 M.N.</strong> (Diez mil pesos 00/100 M.N.) por concepto de apartado para la siguiente propiedad:</p>

        <div class="property-info">
            <p><strong>Dirección:</strong> <?php echo htmlspecialchars($propiedad_direccion); ?></p>
            <p><strong>Precio de Venta Sugerido:</strong> $<?php echo number_format($propiedad_precio_venta, 2); ?></p>
        </div>

        <p>Este recibo es provisional y no representa un contrato de compra-venta.</p>
    </div>
</body>

</html>