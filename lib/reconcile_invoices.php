<?php
/*
    принимает в качестве аргументов массив с платежами по графику и массив с фактическими платежами
    возвращает массив, представляющий сверку расчетов по платежам, начисленным на текущую дату, или false, если нет платежей с наступившим сроком
*/
function reconcile_invoices (array $repayment_plan, array $repayment_fact) {
    //массив, представляющий сверку расчетов
    $reconciliation = array();
    //переменная для его перебора
    $i = -1;  
    //сегодняшняя дата
    $today = new DateTime();
    //временной интервал в 1 день - понадобится для корректного заполнения даты начала просрочки
    $one_day = new DateInterval('P1D');
    //перебираем начисления 
    foreach ($repayment_plan as $pdate => $pamount) {
        ++$i;
        //плановая дата платежа
        $plan_date = new DateTimeImmutable($pdate);
        //накопленные платежи по начислению
        $accumulated_payments = 0;
        //если дата платежа по очередному начислению уже наступила
        if ($plan_date <= $today) {
            //заполняем информацию об очередном начислении и создаем массивы под информацию о платежах и о просрочках по данному начислению
            $reconciliation[$i] = array('billing_info' => array('bill_date' => $pdate, 'bill_sum' => $pamount), 'paying_info' => array(), 'arrears_info' => array());
            //вычислим сумму незачтенных платежей
            $total = 0;
            foreach ($repayment_fact as $date => $sum)
                $total += $sum;
            //если еще остались незачтенные платежи
            if ($total){
                //перебираем платежи
                foreach ($repayment_fact as $fdate => &$famount) {
                    if ($famount == 0)
                        continue;
                    //фактическая дата платежа
                    $fact_date = new DateTime($fdate);
                    //если платеж совершен раньше графика или по графику
                    if ($fact_date <= $plan_date) { 
                        //сравниваем величину остатка начисления и величину платежа
                        switch (($pamount-$accumulated_payments) <=> $famount) {
                            //если остаток начисления равен платежу
                            case 0: 
                                //заполняем информацию о платеже
                                $reconciliation[$i]['paying_info'][] = array ('sum' => $famount, 'date' => $fdate); 
                                //начисление полностью погашено
                                $pamount = 0;
                                //платеж полностью засчитан в уплату данного начисления 
                                $famount = 0; 
                                break;
                            //если остаток начисления больше платежа
                            case 1:
                                //заполняем информацию о платеже 
                                $reconciliation[$i]['paying_info'][] = array ('sum' => $famount, 'date' => $fdate);
                                //накопленное погашение прирастает
                                $accumulated_payments +=$famount;
                                //платеж полностью засчитан в уплату данного начисления 
                                $famount = 0; 
                                break;
                            //если платеж больше остатка начисления
                            case -1:
                                //заполняем информацию о платеже 
                                $reconciliation[$i]['paying_info'][] = array ('sum' => ($pamount - $accumulated_payments), 'date' => $fdate);
                                //платеж частично засчитан в уплату данного остатка начисления 
                                $famount -= ($pamount - $accumulated_payments);
                                //начисление полностью погашено
                                $pamount = 0; 
                                break;
                        }
                    }
                    //если платеж совершен позже графика    
                    else { 
                        //сравниваем величину остатка начисления и величину платежа
                        switch (($pamount-$accumulated_payments) <=> $famount) {
                            //если остаток начисления равен платежу
                            case 0: 
                                //заполняем информацию о просрочке
                                $reconciliation[$i]['arrears_info'][] = array (
                                    'sum' => $pamount - $accumulated_payments,
                                    'start_date' => ($plan_date -> add($one_day)) -> format('d.m.Y'),
                                    'stop_date' => $fact_date -> format('d.m.Y'),
                                    'days' => $plan_date -> diff($fact_date) -> days
                                );
                                //заполняем информацию о платеже
                                $reconciliation[$i]['paying_info'][] = array ('sum' => $famount, 'date' => $fdate);
                                //начисление полностью погашено
                                $pamount = 0;
                                //платеж полностью засчитан в уплату данного начисления 
                                $famount = 0; 
                                break;
                            //если остаток начисления больше платежа
                            case 1:
                                //заполняем информацию о просрочке
                                $reconciliation[$i]['arrears_info'][] = array (
                                    'sum' => $pamount - $accumulated_payments,
                                    'start_date' => ($plan_date -> add($one_day)) -> format('d.m.Y'),
                                    'stop_date' => $fact_date -> format('d.m.Y'),
                                    'days' => $plan_date -> diff($fact_date) -> days
                                );
                                //заполняем информацию о платеже 
                                $reconciliation[$i]['paying_info'][] = array ('sum' => $famount, 'date' => $fdate);
                                //накопленое погашение прирастает
                                $accumulated_payments +=$famount;
                                //платеж полностью засчитан в уплату данного начисления 
                                $famount = 0; 
                                break;
                            //если платеж больше остатка начисления
                            case -1:
                                //заполняем информацию о просрочке
                                $reconciliation[$i]['arrears_info'][] = array (
                                    'sum' => $pamount - $accumulated_payments,
                                    'start_date' => ($plan_date -> add($one_day)) -> format('d.m.Y'),
                                    'stop_date' => $fact_date -> format('d.m.Y'),
                                    'days' => $plan_date -> diff($fact_date) -> days
                                );
                                //заполняем информацию о платеже 
                                $reconciliation[$i]['paying_info'][] = array ('sum' => ($pamount-$accumulated_payments), 'date' => $fdate);
                                //платеж частично засчитан в уплату данного остатка начисления 
                                $famount -= ($pamount - $accumulated_payments);
                                //начисление полностью погашено
                                $pamount = 0; 
                                break;
                        }
                    }
                    //если начисление полностью погашено
                    if ($pamount == 0)
                        //выходим из перебора платежей
                        break;
                }
                unset($famount);
                //если после перебора всех платежей начисление осталось непогашенным
                if ($accumulated_payments < $pamount){
                    //заполняем информацию о просрочке
                    $reconciliation[$i]['arrears_info'][] = array (
                    'sum' => $pamount - $accumulated_payments,
                    'start_date' => ($plan_date -> add($one_day)) -> format('d.m.Y'),
                    //признак наличия текущей просроченной задолженности!
                    'stop_date' => null,
                    'days' => $plan_date -> diff($today) -> days
                    );
                }
            }
            //если все платежи зачтены
            else {
                //заполняем информацию о просрочке по начислению
                $reconciliation[$i]['arrears_info'][] = array (
                    'sum' => $pamount - $accumulated_payments,
                    'start_date' => ($plan_date -> add($one_day)) -> format('d.m.Y'),
                    //признак наличия текущей просроченной задолженности!
                    'stop_date' => null,
                    'days' => $plan_date -> diff($today) -> days
                );
                //и переходим к следующему начислению
                continue;
            }
        }
        //если срок платежа не наступил, то в акт сверки он не попадает
        else if (!sizeof($reconciliation))
            //в эту точку попадаем, если график относится к будущему, т. е. еще вообще не исполнялся
            return false;
        //в эту точку попадаем, если график еще исполняется, останавливаемся на последнем начислении, по которому наступил срок
        else return $reconciliation;
    }
    //в эту точку попадаем, если график полностью исполнен
    return $reconciliation;
}
?>