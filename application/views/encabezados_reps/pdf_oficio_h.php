<table width="1120px" style="font-size: 12px;">
    <tr>
        <td rowspan="2" width="75px" style="text-align: center" >
            <img src="images/logo_entidad_pq.png" alt="test alt attribute" width="80" height="80" border="0" />
        </td>
        <td  width="970px" style="text-align: center">
            <b>REPUBLICA DE COLOMBIA<br>
                GOBERNACIÓN DEL PUTUMAYO<br>
                NIT: 800094164-4
            </b>
            <br>
            <i>"JUNTOS PODEMOS TRANSFORMAR"</i>
        </td>
        <td rowspan="2" style="text-align: center" width="75px">
            <img src="images/logo_admin_3x3.png" alt="test alt attribute" width="80" height="80" border="0" />
        </td>
    </tr>

    <tr>
        <td style="text-align: center">
            <?php
            echo (isset($titulo)) ? $titulo : 'NÓMINA EMPLEADOS GOBERNACIÓN';
            echo (isset($periodo)) ? '<br>'.$periodo : '';
            ?>
        </td>
    </tr>
</table>
