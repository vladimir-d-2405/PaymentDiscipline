<!doctype html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Загрузка файлов</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous" defer></script>
    </head>
    <body class="container pt-5">
        <div class="m-auto border w-50 shadow-lg p-3 mb-5 bg-body-tertiary rounded">
            <form action="handlers/discipline_report.php" method="POST" enctype="multipart/form-data">
                <div class="container mb-3">
                    <label class="form-label" for="plan">Выберите файл .csv с плановыми платежами</label>
                    <input type="file" class="form-control" id="plan" name="file_name[]">        
                </div>
                <div class=" container mb-3">
                    <label class="form-label" for="fact">Выберите файл .csv с фактическими платежами</label>
                    <input type="file" class="form-control" id="fact" name="file_name[]">        
                </div>
                <div class="container text-center mb-3">
                    <button type="submit" class="btn btn-outline-secondary">Сформировать отчет о платежной дисциплине</button>
                </div>    
            </form>
        </div>
    </body>
</html>
