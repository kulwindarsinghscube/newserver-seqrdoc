<?php

// Dashboard
Breadcrumbs::for('dashboard', function ($trail) {
    $trail->push('Dashboard', route('admin.dashboard'));
});

// Customs
Breadcrumbs::for('customs', function ($trail) {
    $trail->push('Customs');
});

// Statement Of Marksheet
Breadcrumbs::for('generatebmcccertificates', function ($trail) {
    $trail->parent('customs');
    $trail->push('BMCC SOM', route('bmcc-certificate.uploadpage'));
});


Breadcrumbs::for('generatewoxsencertificates', function ($trail) {
    $trail->parent('customs');
    $trail->push('WOXSEN SOM', route('woxsen-certificate.uploadpage'));
});

Breadcrumbs::for('generatebmccpassingcertificates', function ($trail) {
    $trail->parent('customs');
    $trail->push('BMCC Passing Certificate', route('bmcc-certificate.uploadpagePassing'));
});

Breadcrumbs::for('generatemitadtpassingcertificates', function ($trail) {
    $trail->parent('customs');
    $trail->push('MIT Passing Certificate', route('passing-certificate.uploadpage'));
});

// Statement Of Marksheet
Breadcrumbs::for('generatefergussoncertificates', function ($trail) {
    $trail->parent('customs');
    $trail->push('Fergusson SOM', route('fergusson-certificate.uploadpage'));
});
// Custom Template Master
Breadcrumbs::for('customtemplatemaster', function ($trail) {
    $trail->parent('customs');
    $trail->push('Template Master', route('uasb-certificate.index'));
});
Breadcrumbs::for('generategalgotiascertificates', function ($trail) {
    $trail->parent('customs');
    $trail->push('Generate Certificate', route('galgotias-certificate.uploadpage'));
});
// KESSC
Breadcrumbs::for('generatekessccertificates', function ($trail) {
    $trail->parent('customs');
    $trail->push('KESSC Certificate', route('kessc-certificate.uploadpage'));
});
// AURO
Breadcrumbs::for('generateauroertificates', function ($trail) {
    $trail->parent('customs');
    $trail->push('AURO Certificate', route('auro-certificate.uploadpage'));
});
//RRMU
Breadcrumbs::for('generaterrmucertificates', function ($trail) {
    $trail->push('RRMU Certificate', route('rrmu-certificate.uploadpage'));
});


//CHANAKYA
Breadcrumbs::for('generatechanakyacertificates', function ($trail) {
    $trail->push('Chanakya Certificate', route('chanakya-certificate.uploadpage'));
});


//KSG
Breadcrumbs::for('generateksgcertificates', function ($trail) {
    $trail->push('KSG Certificate', route('ksg-batch.uploadpage'));
});

Breadcrumbs::for('Branch', function ($trail) {
    $trail->parent('customs');
    $trail->push('Branch', route('ksg-branch.uploadpage'));
});

Breadcrumbs::for('Print', function ($trail) {
    $trail->parent('customs');
    $trail->push('Print', route('ksg-print.page'));
});
Breadcrumbs::for('printpage', function ($trail) {
    $trail->parent('Print');
    $trail->push('Records', route('ksg-approval.page'));
});


Breadcrumbs::for('generatetpsdicertificates', function ($trail) {
    $trail->parent('dashboard');
    $trail->parent('customs');
    $trail->push('TPSDI Certificate', route('tpsdi-certificate.uploadpage'));
});   



//Cavendish
Breadcrumbs::for('generatecavendishcertificates', function ($trail) {
    $trail->push('Cavendish Certificate', route('cavendish-certificate.uploadpage'));
});

// ICCS
Breadcrumbs::for('generateiccscertificates', function ($trail) {
    $trail->parent('customs');
    $trail->push('ICCS Certificate', route('iccs-certificate.uploadpage'));
});
// MONAD
Breadcrumbs::for('generatemonadcertificates', function ($trail) {
    $trail->parent('customs');
    $trail->push('MONAD Certificate', route('monad-certificate.uploadpage'));
});
// SPIT
Breadcrumbs::for('generatespitcertificates', function ($trail) {
    $trail->parent('customs');
    $trail->push('Grade Cards', route('spit-certificate.uploadpage'));
});

// LNCT Bhopal
Breadcrumbs::for('generatelnctcertificates', function ($trail) {
    $trail->parent('customs');
    $trail->push('LNCT Degree Certificate', route('lnct-certificate.uploadpage'));
});

// Ghribmjal
Breadcrumbs::for('generateghribmjalcertificates', function ($trail) {
    $trail->parent('customs');
    $trail->push('Ghribmjal Certificate', route('ghribmjal-certificate.uploadpage'));
});
// KMTC
Breadcrumbs::for('generatekmtccertificates', function ($trail) {
    $trail->parent('customs');
    $trail->push('KMTC Documents', route('kmtc-certificate.uploadpage'));
});
//Excel2Pdf
Breadcrumbs::for('exceltopdf', function ($trail) {
    //$trail->parent('customs');
    $trail->push('Excel To PDF', route('pdf2pdf.excel2pdf'));
});
// Generate PDF
Breadcrumbs::for('generatepdf', function ($trail) {
    $trail->parent('customs');
    $trail->push('Generate PDF', route('exceltopdf.uploadpage'));
});
// Document Setup
Breadcrumbs::for('documentsetup', function ($trail) {
    $trail->parent('dashboard');
    $trail->push('Document Setup');
});

// Home > Document Setup > [Template Management]
Breadcrumbs::for('templatemangement', function ($trail) {
    $trail->parent('documentsetup');
    $trail->push('Template Management', route('template-master.index'));
});

// Home > Document Setup > [Font Master]
Breadcrumbs::for('fontmaster', function ($trail) {
    $trail->parent('documentsetup');
    $trail->push('Font Master', route('fontmaster.index'));
});

// Home > Document Setup > [Font Master]
Breadcrumbs::for('backgroundmaster', function ($trail) {
    $trail->parent('documentsetup');
    $trail->push('Bacground Template Management', route('background-master.index'));
});

// Home > Document Setup > [Process Excel]
Breadcrumbs::for('processexcel', function ($trail) {
    $trail->parent('documentsetup');
    $trail->push('Process Excel', route('processExcel.index'));
});

// Home > Document Setup > [Dynamic Image management]
Breadcrumbs::for('dynamicimage', function ($trail) {
    $trail->parent('documentsetup');
    $trail->push('Dynamic Image management', route('dynamic-image-management.index'));
});
// Home > Document Setup > [Generate Id Card]
Breadcrumbs::for('generateidcard', function ($trail) {
    $trail->parent('documentsetup');
    $trail->push('Generate Id Cards', route('idcards.index'));
});
// Home > Document Setup > [Id Card Status]
Breadcrumbs::for('idcardstatus', function ($trail) {
    $trail->parent('documentsetup');
    $trail->push('Id cards status', route('idcard-status.index'));
});

// Payment Setup
Breadcrumbs::for('paymentsetup', function ($trail) {
    $trail->parent('dashboard');
    $trail->push('Payment Setup');
});

// Home > Payment Setup > [Template Management]
Breadcrumbs::for('paymentgateway', function ($trail) {
    $trail->parent('paymentsetup');
    $trail->push('Payment Gateway', route('pgmaster.index'));
});

Breadcrumbs::for('paymentgateway_new', function ($trail) {
    $trail->parent('paymentsetup');
    $trail->push('Payment Gateway New', route('pgmaster_new.index'));
});

// Home > Payment Setup > [PG onfiguration]
Breadcrumbs::for('pgconfig', function ($trail) {
    $trail->parent('paymentsetup');
    $trail->push('PG onfiguration', route('pgconfig.index'));
});

// Home > Payment Setup > [PG onfiguration ]   -> Rohit
Breadcrumbs::for('pg_newconfig', function ($trail) {
    $trail->parent('paymentsetup');
    $trail->push('PG Configuration', route('pg_newconfig.index'));
});

// Document Management
Breadcrumbs::for('documentmanagement', function ($trail) {
    $trail->parent('dashboard');
    $trail->push('Document Management');
});

// Home > Document Management > [Certificate Management]
Breadcrumbs::for('certificatemanagement', function ($trail) {
    $trail->parent('documentmanagement');
    $trail->push('Certificate Management', route('certificateManagement.index'));
});

// Home >Document Management > [Printing Details]
Breadcrumbs::for('printingdetail', function ($trail) {
    $trail->parent('documentmanagement');
    $trail->push('Printing Details', route('printing-detail.index'));
});

// System Config
Breadcrumbs::for('systemconfig', function ($trail) {
    $trail->parent('dashboard');
    $trail->push('System Config');
});

// Home > System Config > [Institute Management]
Breadcrumbs::for('institutemanagement', function ($trail) {
    $trail->parent('systemconfig');
    $trail->push('Institute Management', route('institutemaster.index'));
});

// Home > System Config > [User Management]
Breadcrumbs::for('usermanagement', function ($trail) {
    $trail->parent('systemconfig');
    $trail->push('User Management', route('usermaster.index'));
});

// Home > System Config > [Admin Management]
Breadcrumbs::for('adminmanagement', function ($trail) {
    $trail->parent('systemconfig');
    $trail->push('Admin Management', route('adminmaster.index'));
});

// Home > System Config > [Roles Management]
Breadcrumbs::for('rolemanagement', function ($trail) {
    $trail->parent('systemconfig');
    $trail->push('Roles Management', route('roles.index'));
});

// Home > System Config > [Settings]
Breadcrumbs::for('setting', function ($trail) {
    $trail->parent('systemconfig');
    $trail->push('Settings', route('systemconfig.index'));
});

// Home > System Config > [Settings]
Breadcrumbs::for('studentmanagement', function ($trail) {
    $trail->parent('systemconfig');
    $trail->push('Student Management', route('systemconfig.index'));
});

// Reports
Breadcrumbs::for('report', function ($trail) {
    $trail->parent('dashboard');
    $trail->push('Reports');
});

// Home > Reports > [Template Data]
Breadcrumbs::for('templatedata', function ($trail) {
    $trail->parent('report');
    $trail->push('Template Data', route('template-data.index'));
});

// Home > Reports > [Printing Report]
Breadcrumbs::for('printingreport', function ($trail) {
    $trail->parent('report');
    $trail->push('Printing Report', route('printer-report.index'));
});

// Home > Reports > [Scan History]
Breadcrumbs::for('scanhistory', function ($trail) {
    $trail->parent('report');
    $trail->push('Scan History', route('scanHistory.index'));
});

// Home > Reports > [Payment Transactions]
Breadcrumbs::for('paymenttransaction', function ($trail) {
    $trail->parent('report');
    $trail->push('Payment Transactions', route('transaction.index'));
});

// Home > Reports > [User Session Manager]
Breadcrumbs::for('usersession', function ($trail) {
    $trail->parent('report');
    $trail->push('User Session Manager', route('session-manager.index'));
});

// Master
Breadcrumbs::for('master', function ($trail) {
    $trail->parent('dashboard');
    $trail->push('Masters');
});

// Home > Masters > [Semester]
Breadcrumbs::for('semester', function ($trail) {
    $trail->parent('master');
    $trail->push('Semester', route('semester.index'));
});

// Home > Masters > [Semester]
Breadcrumbs::for('branch', function ($trail) {
    $trail->parent('master');
    $trail->push('Branch', route('branch.index'));
});

// Home > Masters > [Session]
Breadcrumbs::for('sessionsmasterpage', function ($trail) {
    $trail->parent('master');
    $trail->push('Session', route('sessionsmaster.index'));
});

// Home > Masters > [Degree]
Breadcrumbs::for('degreemasterpage', function ($trail) {
    $trail->parent('master');
    $trail->push('Degree', route('degreemaster.index'));
});

// Stock
Breadcrumbs::for('stock', function ($trail) {
    $trail->parent('dashboard');
    $trail->push('Stock');
});

// Home > Stock > [Stationary Stock]
Breadcrumbs::for('stationarystock', function ($trail) {
    $trail->parent('stock');
    $trail->push('Stationary Stock', route('stationarystock.index'));
});

// Home > Stock > [Damaged Stock]
Breadcrumbs::for('damagedstock', function ($trail) {
    $trail->parent('stock');
    $trail->push('Damaged Stock', route('damagedstock.index'));
});

// Home > Stock > [ Consumption Report]
Breadcrumbs::for('consumptionreport', function ($trail) {
    $trail->parent('stock');
    $trail->push(' Consumption Report', route('consumptionreport.index'));
});

// Home > Stock > [ Consumption Report Export]
Breadcrumbs::for('consumptionreportexport', function ($trail) {
    $trail->parent('stock');
    $trail->push('Download Consumption Report', route('consumptionreportexport.index'));
});

// Dashboard > Customs > [Intifacc Mordern Technology]
Breadcrumbs::for('imtcertificate', function ($trail) {
    $trail->parent('dashboard');
    $trail->parent('customs');
    $trail->push('Intifacc Mordern Technology', route('imt.index'));
});


// Dashboard > Customs > [Intifacc Mordern Technology]
Breadcrumbs::for('generatechanakyaucertificates', function ($trail) {
    $trail->parent('dashboard');
    $trail->parent('customs');
    $trail->push('Chanakya Certificate', route('chanakya-certificate.uploadpage'));
});


//yuva parivartan breadcrums
Breadcrumbs::for('generateyuvaparivartancertificates', function ($trail) {
    $trail->parent('dashboard');
    $trail->parent('customs');
    $trail->push('Yuva Parivartan Certificate', route('yuvaparivartan-certificate.uploadpage'));
});   


// Dashboard > Customs > [Temporary Travel Document]
Breadcrumbs::for('ttdcertificate', function ($trail) {
    $trail->parent('dashboard');
    $trail->parent('customs');
    $trail->push('Temporary Travel Document', route('ttd.index'));
});
//KSG
Breadcrumbs::for('Batch', function ($trail) {
    $trail->parent('customs');
    $trail->push('Batch', route('ksg-batch.uploadpage'));
});
Breadcrumbs::for('batchpagepage', function ($trail) {
    $trail->parent('Batch');
    $trail->push('Records', route('ksg-batch.uploadpage'));
});
Breadcrumbs::for('approvalpage', function ($trail) {
    $trail->parent('Approval Page');
    $trail->push('Records', route('ksg-approval.page'));
});

Breadcrumbs::for('Approval Page', function ($trail) {
    $trail->parent('customs');
    $trail->push('Approval Page', route('ksg-approval.page'));
});

Breadcrumbs::for('generateyuvaparivartanverification', function ($trail) {
    $trail->parent('customs');
    $trail->push('Yuva Parivartan ', route('yuvaparivartan-records.uploadpage'));
});

//SURYODAYA COLLEGE OF ENGINEERING & TECHNOLOGY
Breadcrumbs::for('generatesuryodayacerification', function ($trail) {
    $trail->parent('customs');
    $trail->push('SURYODAYA COLLEGE OF ENGINEERING & TECHNOLOGY ', route('suryodaya-certificate.uploadpage'));
});