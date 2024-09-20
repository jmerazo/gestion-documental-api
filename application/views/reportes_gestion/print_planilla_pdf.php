<style>
    .tbBorder {
        width: 100%;
        border: 1px solid #000;
        border-collapse: collapse;
    }

    .tbBorder th, .tbBorder td {
        border: 1px solid #000;
    }

    .fotn-size-min {
        font-size: 8pt !important;
    }

    table {
        page-break-after: avoid;
    }

    .salto {
        page-break-inside: avoid;
        margin: 0px;
        padding: 0px;
        overflow: hidden;
        height: 1px;
    }
</style>
<table class="tbBorder fotn-size-min" cellspacing="0">
    <thead>
    <tr>
        <th width="160px">Radicado</th>
        <th width="25%">Inf. Documento</th>
        <th width="25%">Asunto</th>
        <th width="15%">Asignado a</th>
        <th width="20%">Firma</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (isset($data)):
        foreach ($data as $rd) :
            //$asignado = explode(",", $rd['asignado_a']);
            //$nroFils = count($asignado);
            $nroFils = 1;
            $rd['fun_dest'] = json_decode('[' . $rd['fun_dest'] . ']', true);
            $nroDestino = count($rd['fun_dest']);
            $nroDestino = $nroDestino == 0 ? 1 : $nroDestino;
            #var_dump($rd['fun_dest']);
            ?>

            <tr class="align-middle">
                <td width="160px" rowspan="<?php echo $nroDestino ?>">
                    <span><b>Nro Rad:</b> <?php echo $rd['nro_rad'] ?><br></span>
                    <span><b>Nro Doc:</b> <?php echo empty($rd['nro_doc']) ? 'ND' : $rd['nro_doc'] ?><br></span>
                    <span><b>Fec. Doc:</b> <?php echo $rd['fec_doc'] ?><br></span>
                </td>
                <td width="25%" rowspan="<?php echo $nroDestino ?>">
                    <?php
                    $dtsTercero = json_decode($rd['dts_tercero']);
                    $nomTer = 'ND';
                    $entTer = 'ND';
                    if (!is_null($dtsTercero)) {
                        #var_dump($dtsTercero);
                        $nomTer = isset($dtsTercero->nombre) ? $dtsTercero->nombre : 'ND';
                        $entTer = isset($dtsTercero->entidad) ? $dtsTercero->entidad : 'ND';
                    }
                    ?>
                    <span><b>Ent Remite:</b> <?php echo $entTer ?><br></span>
                    <span><b>Per Remite:</b> <?php echo $nomTer ?><br></span>
                    <span><b>Fec Rta:</b><?php echo empty($rd['fec_rta_doc']) ? 'NA' : $rd['fec_rta_doc'] ?>
                        <br></span>
                    <span><b>Tip. Doc:</b> <?php echo '' ?></span>
                </td>
                <td width="25%" rowspan="<?php echo $nroDestino ?>"><?php echo $rd['asunto'] ?></td>
                <td height="15%" width="15%">
                    <?php echo Capitalizar(@$rd['fun_dest'][0]['nomapes']) ?>
                </td>
                <td width="20%"></td>
            </tr>

            <?php
            if ($nroDestino > 1) {
                for ($i = 1; $i < $nroDestino; $i++) {
                    echo '<tr>';
                    echo '<td height="30px">' . Capitalizar(@$rd['fun_dest'][$i]['nomapes']) . '</td>';
                    echo '<td></td>';
                    echo '</tr>';

                }
            }
            ?>
        <?php
        endforeach;
    endif;
    ?>
    </tbody>
</table>