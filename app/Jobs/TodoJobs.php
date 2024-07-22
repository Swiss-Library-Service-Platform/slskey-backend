<?php
/*
________________________________________________________________________
Before Go Live:

TODO: Add Cron Job for Jobs

TODO: TEST: Server aufbauen

TODO: Deployment Produktive
      - Laravel Octane
      - PHP FPM

TODO: Was ist mit edu-ID Login
      - Erstmal auf Eis

TODO: Bug Cloud App
      - Wenn man keine Rechte hat
      - Aber schon im User Record ist
      - Wird "Access Denied" übersprungen und direkt zum User gegangen

TODO: Cloud App, get Staffuser
      - man sollte nur detail call /users/admin nutzen
      - andernfalls können mehrere User zurüpckgegeben werden z.b. bei "admin" -> "Admin_ZHDK_3", ...
      - und dann fehler

________________________________________________________________________
Nice to have:

TODO: Cloud App
      - Icon Jiggle Bug wenn geöffnet wird

TODO: Participage page: Motivation / Vorteile aufzählen

TODO: Wrap activation in Transaction
      -> mock a transaction for switch service api
      -> remove first switch group from user when first was successful and second failed

TODO: Test:
      - for each job
      - Add good test for Cloud App Auths
      - Add good test for Alma Api Service (its all mocked)

TODO: können wir mitbekommen wenn Switch edu-ID gelöscht wurde??

TODO: Healthcheck, wo System überprüft wird
      - API
      - edu-ID API
      - Alma API

________________________________________________________________________
Feedback Bibliotheken:

TODO: User needs some kind of "affiliation"? e.g. welche Bibliothek, welche Abteilung, etc.
      Beispiel: Aargau: sie wollen wissen, welche Bib genau einen User freigeschaltet hat

________________________________________________________________________
Import Tool

TODO: user group sind IZ spezifisch
-> was bedeutet das: ImportTool Verifier sind nicht wirklich korrekt, weil sie nur die NZ API verwenden!
-> außer das Import Tool kann mit IZ API arbeiten statt NZ
-> wir brauchen jetzt auch Alma IZ API, also könnten wir das auch umstellen, theoretisch??

*/
