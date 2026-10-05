<?php
declare(strict_types=1);

use Dompdf\Dompdf;
use Dompdf\Options;

function mask_pan(string $pan): string
{
    return substr($pan, 0, 2) . 'XXXXX' . substr($pan, 7);
}

function receipt_vars(array $d): array
{
    $t = cfg('trust');
    return [
        'trust_name' => $t['name'],
        'reg_no' => $t['reg_no'],
        'address' => $t['address'],
        'trust_email' => $t['email'],
        'tax_note' => $t['80g'],
        'receipt_no' => $d['receipt_no'],
        'date' => date('d M Y', strtotime($d['approved_at'] ?? 'now')),
        'name' => $d['name'],
        'pan' => $d['pan'],
        'phone' => $d['phone'],
        'email' => $d['email'],
        'amount' => number_format((float)$d['amount'], 2),
    ];
}

/** Returns the PDF binary. The logo is inlined as a data URI so Dompdf needs no remote access. */
function receipt_pdf(array $d): string
{
    $html = render('receipt_pdf.html', receipt_vars($d));
    $logo = cfg('trust')['logo'];
    $uri = is_file($logo) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logo)) : '';
    $html = str_replace('{{logo_src}}', $uri, $html);

    $o = new Options();
    $o->set('isRemoteEnabled', false);
    $pdf = new Dompdf($o);
    $pdf->loadHtml($html);
    $pdf->setPaper('A4');
    $pdf->render();
    return $pdf->output();
}
