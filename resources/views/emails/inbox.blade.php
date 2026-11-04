<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hotel Mail Testing Inbox</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f3f5f9; font-family:Arial,sans-serif; }
        .mail-header { background:linear-gradient(90deg,#17365d,#2874a6); color:#fff; }
        .brand { font-weight:700; letter-spacing:.3px; }
        .inbox-card { border:0; border-radius:14px; box-shadow:0 4px 18px #17365d18; overflow:hidden; }
        .message-row { border-bottom:1px solid #e8edf3; }
        .message-row:last-child { border-bottom:0; }
        .message-body { background:#fff; min-height:260px; }
        .pill { background:#dff5e7; color:#18733a; border-radius:999px; padding:4px 10px; font-size:.78rem; font-weight:700; }
    </style>
</head>
<body>
<header class="mail-header py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="brand">Robert Hotel Mail Testing Inbox</div>
        <span class="badge bg-light text-primary">LOCAL • MAILTRAP STYLE</span>
    </div>
</header>
<main class="container py-4">
    <div class="row g-4">
        <aside class="col-lg-3">
            <div class="inbox-card bg-white p-3">
                <h5 class="mb-3">Inbox</h5>
                <div class="list-group">
                    <div class="list-group-item active">Purchase Order Notifications <span class="badge bg-light text-primary float-end">1</span></div>
                    <div class="list-group-item">Sent</div>
                    <div class="list-group-item">Email Testing</div>
                </div>
                <p class="small text-muted mt-3 mb-0">This local inbox safely previews outgoing Laravel emails without sending to real guests.</p>
            </div>
        </aside>
        <section class="col-lg-9">
            <div class="inbox-card bg-white">
                <div class="p-4 message-row">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h3 class="mb-3">Purchase order {{ $order->po_number }}</h3>
                            <div class="small text-muted">From: Robert Hotel Inventory &lt;no-reply@roberthotel.test&gt;</div>
                            <div class="small text-muted">To: Hotel Manager &lt;manager@example.com&gt;</div>
                            <div class="small text-muted">Subject: Purchase Order {{ $order->po_number }} status update</div>
                        </div>
                        <span class="pill">Delivered to test inbox</span>
                    </div>
                </div>
                <div class="message-body p-4">
                    {!! $mailBody !!}
                </div>
            </div>
        </section>
    </div>
</main>
</body>
</html>
