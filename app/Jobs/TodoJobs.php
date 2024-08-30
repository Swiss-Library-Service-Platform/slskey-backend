<?php
/*
________________________________________________________________________
Before Go Live:

TODO: Fix all FIXMEs before Go Live!

TODO: Add Cron Job for Jobs

TODO: TEST: Server aufbauen

TODO: Deployment Produktive
      - Laravel Octane
      - PHP FPM

TODO: Job Supervisor für ImportCsvJob
      https://laravel.com/docs/11.x/queues#installing-supervisor
            Wir müssen den php artisan queue starten
            automatisch durch supervisor
            default queue genügt
            auch noch in [README.md](http://README.md) mit aufnehmen
      php artisan queue:work --queue=redis_import_job --timeout=120

________________________________________________________________________
Nice to have:

TODO: Verifier
      - mehr infos sehen im Portal
      - vielleicht sogar als Bibliotheksuser??

TODO: Cloud App
      - Icon Jiggle Bug wenn geöffnet wird

TODO: Participage page: Motivation / Vorteile aufzählen

TODO: Test:
      - for each job
      - Add good test for Cloud App Auths
      - Add good test for Alma Api Service (its all mocked)

TODO: können wir mitbekommen wenn Switch edu-ID gelöscht wurde??

________________________________________________________________________
Feedback Bibliotheken:

TODO: User needs some kind of "affiliation"? e.g. welche Bibliothek, welche Abteilung, etc.
      Beispiel: Aargau: sie wollen wissen, welche Bib genau einen User freigeschaltet hat

TODO: is_member_educational_institution for ZHDK
      - Cloud App Anpassung
      - Import CSV Anpassung
      - Ui Anpassung, erklärung education institution
      - in Monthly Report aufnehmen

*/
