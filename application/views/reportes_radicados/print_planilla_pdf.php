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
        foreach ($data

                 as $rd) :
            //$asignado = explode(",", $rd['asignado_a']);
            //$nroFils = count($asignado);
            $nroFils = 1;
            ?>
            <tr class="align-middle">
                <td width="160px">
                    <span><b>Nro Rad:</b> <?php echo $rd['nro_rad'] ?><br></span>
                    <span><b>Nro Doc:</b> <?php echo empty($rd['nro_doc']) ? 'ND' : $rd['nro_doc'] ?><br></span>
                    <span><b>Fec. Doc:</b> <?php echo $rd['fec_doc'] ?><br></span>
                </td>
                <td width="25%">
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
                <td width="25%"><?php echo $rd['asunto'] ?></td>
                <td height="15%" width="15%"><?php echo Capitalizar($rd['fun_dest']) . '<br>' . Capitalizar($rd['ofi_dest']) ?></td>
                <td width="20%"></td>
            </tr>

        <?php
        endforeach;
    endif;
    ?>
    </tbody>
</table>