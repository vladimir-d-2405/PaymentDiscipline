<?php
    declare(strict_types=1);
    require_once ('../lib/reconcile_invoices.php');
    $plan_file = fopen($_FILES['file_name']['tmp_name'][0],'r');
    flock($plan_file,LOCK_SH);
    $i = 0;
    while ($plan_array_raw[] = fgetcsv($plan_file)){
        $plan_array[$plan_array_raw[$i][0]] = $plan_array_raw[$i][1];
        ++$i;
    }
    flock($plan_file,LOCK_UN);
    fclose($plan_file);
    $fact_file = fopen($_FILES['file_name']['tmp_name'][1],'r');
    flock($fact_file,LOCK_SH);
    $i = 0;
    while ($fact_array_raw[] = fgetcsv($fact_file)){
        $fact_array[$fact_array_raw[$i][0]] = $fact_array_raw[$i][1];
        ++$i;
    }
    flock($fact_file,LOCK_UN);
    fclose($fact_file);
    $reconciliation = reconcile_invoices($plan_array, $fact_array);
    echo <<< LABEL
    <!doctype html>
    <html lang="ru">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Отчет о платежной дисциплине клиента</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous" defer></script>
            <style> table {
                        table-layout: fixed ;
                        width: 100% ;
                    }
                    td {
                    width: 12.5% ;
                    }
            </style> 
        </head>
        <body class="container pt-5">
            <div class="m-auto border shadow-lg p-3 mb-5 bg-body-tertiary rounded word-wrap">
                <table class="table table-bordered caption-top table-striped">
                    <caption><a class="link-secondary" href="../index.php">На главную</a></caption>
                    <thead class="align-middle text-center">
                        <tr>
                            <th scope="col" colspan="2">Начисление</th>
                            <th scope="col" colspan="2">Оплата</th>
                            <th scope="col" colspan="4">Просрочка</th>
                        </tr>
                        <tr>
                            <th scope="col">Дата</th>
                            <th scope="col">Сумма</th>
                            <th scope="col">Дата</th>
                            <th scope="col">Сумма</th>
                            <th scope="col">Сумма</th>
                            <th scope="col">Дата начала</th>
                            <th scope="col">Дата окончания</th>
                            <th scope="col">Продолжи&shyтельность в днях</th>
                        </tr>    
                    </thead>
                    <tbody class="align-middle">
    LABEL;
    foreach ($reconciliation as $line) {
        //$rawspan = max(1, sizeof($line['paying_info']),sizeof($line['arrears_info']));
        echo <<< LABEL
                        <tr>
                            <td class="text-center">{$line['billing_info']['bill_date']}</td>
                            <td class="text-end pe-3">{$line['billing_info']['bill_sum']}</td>
                            <td class="text-center">
        LABEL;
        if (sizeof($line['paying_info']))
            foreach ($line['paying_info'] as $payments)
                echo 
                                "{$payments['date']}<br>";
        else
            echo
                                "x";
        echo
                            "</td>";
        if (sizeof($line['paying_info'])) {
        echo
                            "<td class=\"text-end pe-3\">";
            foreach ($line['paying_info'] as $payments)
                echo 
                                "{$payments['sum']}<br>";
        }
        else
            echo
                            "<td class=\"text-center\">
                                x";
        echo
                            "</td>";
        if (sizeof($line['arrears_info'])) {
            echo
                            "<td class=\"text-end pe-3\">";
            foreach ($line['arrears_info'] as $arrears)
                echo 
                                "{$arrears['sum']}<br>";
        }
        else
            echo
                            "<td class=\"text-center\">
                                x";
        echo
                            "</td>
                            <td class=\"text-center\">";
        if (sizeof($line['arrears_info']))
            foreach ($line['arrears_info'] as $arrears)
                echo 
                                "{$arrears['start_date']}<br>";
        else
                echo
                                "x";
        echo
                            "</td>
                            <td class=\"text-center\">";
        if (sizeof($line['arrears_info']))
            foreach ($line['arrears_info'] as $arrears)
                echo 
                                "{$arrears['stop_date']}<br>";
        else
                echo
                                "x";
        echo
                            "</td>
                            <td class=\"text-center\">";
        if (sizeof($line['arrears_info']))
            foreach ($line['arrears_info'] as $arrears)
                echo 
                                "{$arrears['days']}<br>";
        else
            echo
                                "x";
        echo
                            "</td>
                            
                        </tr>";
    }
    echo <<< LABEL
                    </tbody>
                </table>
            </div>
            <p></p>
        </body>
    </html>
    LABEL;
?>