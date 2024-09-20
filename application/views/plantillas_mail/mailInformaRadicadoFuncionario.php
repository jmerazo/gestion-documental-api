<style>
    .normal {
        width: 100;
        border: 1px solid #000;
        border-collapse: collapse;
    }
    .normal th, .normal td {
        border: 1px solid #000;
    }
</style>
<p>
    Cordial saludo.<br><br>
    Se le ha asignado un nuevo documento desde la ventanilla única de la Gobernación del Putumayo para que realice su respectiva gestión.
</p>
<p>
    Datos del documento:
</p>
<table border="1" class="normal">
    <tbody>
    <tr>
        <td style="width: 25%"><b>Tipo documento: </b></td>
        <td><?php echo isset($tipo_documento) ? $tipo_documento : '' ?></td>
    </tr>
    <tr>
        <td style="width: 25%"><b>Radicado número: </b></td>
        <td><?php echo isset($radicado_documento) ? $radicado_documento : '' ?></td>
    </tr>

    <tr>
        <td><b>Fecha:</b></td>
        <td><?php echo date('Y-m-d H:i:m') ?></td>
    </tr>
    <tr>
        <td><b>Asunto:</b></td>
        <td><?php echo isset($asunto_documento) ? $asunto_documento : '' ?></td>
    </tr>
    <tr>
        <td><b>Detalles:</b></td>
        <td><?php echo isset($notasdoc) ? $notasdoc : '' ?></td>
    </tr>

    <tr>
        <td><b>Fecha limite respuesta:</b></td>
        <td><?php echo isset($fecha_respuesta) ? $fecha_respuesta : 'NA' ?></td>
    </tr>
    </tbody>
</table>
<br>
<p>
    Para ver el documento ingrese a <?php echo base_url() ?><br><br>

    Si no conoce el usuario y la contraseña de ingreso, diríjase a la Oficina de Sistemas de Información de la Gobernación del Putumayo.
</p>
<br>
<p>
    Atentamente,
    <br><br><br>

    OFICINA DE VENTANILLA UNICA<br>
    Gobernación del Putumayo<br>
    <i>"Juntos podemos transformar"</i>
</p>
<br><br>
<p style="font-family: verdana; font-size: 11px">
    Esta dirección de correo electrónico es utilizada solamente para notificaciones automáticas de correspondencia asignada a usted, su oficina o secretaría. POR FAVOR NO RESPONDA ESTE CORREO.
    <br><br>
    Si tiene alguna inquietud, puede hacerlo remitiendo su solicitud a la cuenta de correo electrónico contactenos@putumayo.gov.co.
    <br><br>
    <i>
        Confidencialidad: La información contenida en este mensaje de e-mail y sus anexos, es confidencial y está reservada para el destinatario únicamente. Si usted no es el destinatario o un empleado o agente responsable de enviar este mensaje al destinatario final, se le notifica que no está autorizado para revisar, retransmitir, imprimir, copiar, usar o distribuir este e-mail o sus anexos. Si usted ha recibido este e-mail por error, por favor comuníquelo inmediatamente vía e-mail al remitente y tenga la amabilidad de borrarlo de su computadora o cualquier otro banco de datos. Muchas gracias.
        <br><br>
        Confidentiality Notice: The information contained in this email message, including any attachment, is confidential and is intended only for the person or entity to which it is addressed. If you are neither the intended recipient nor the employee or agent responsible for delivering this message to the intended recipient, you are hereby notified that you may not review, retransmit, convert to hard copy, copy, use or distribute this email message or any attachments to it. If you have received this email in error, please contact the sender immediately and delete this message from any computer or other data bank. Thank you.
    </i>
</p>
<img src="http://www.putumayo.gov.co/images/baner_header.png" alt="encabezado" width="700" height="109"><br><br>

