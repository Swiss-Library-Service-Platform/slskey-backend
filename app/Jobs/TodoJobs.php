<?php
/*
________________________________________________________________________
Before Go Live:


TODO: Prod Server
      - Add Cron Job for Jobs

TODO: Job Supervisor für ImportCsvJob
      https://laravel.com/docs/11.x/queues#installing-supervisor
            Wir müssen den php artisan queue starten
            automatisch durch supervisor
            default queue genügt
            auch noch in [README.md](http://README.md) mit aufnehmen
      php artisan queue:work --queue=redis_import_job --timeout=120

TODO: Fix all FIXMEs before Go Live!

TODO: is_member_educational_institution for ZHDK
      - Cloud App Anpassung

TODO: Import CSV Job
      - sollte nicht aktivieren wenn es eine Migration ist
      - alternative: keine switch gruppe für slskey gruppe definieren und check ausgeklammert lassen dass es keine slskey gruppe braucht für aktivierung

TODO: Sortier bug
      - es sortiert nicht richtig nach activatin date
________________________________________________________________________
Later, nice to have:

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

*/
