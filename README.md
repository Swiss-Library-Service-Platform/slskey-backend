# SLSKey - E-Resource Authentication

<!-- Logo -->
<p align="left">
  <picture>
    <source width="300" media="(prefers-color-scheme: dark)" srcset="./public/images/slskey_logo_full_white.png">
    <img width="300" alt="Shows black logo on white mode." src="/public/images/slskey_logo_full_black.png">
  </picture>
</p>

## A Service by Swiss Library Service Platform (SLSP)

<!-- Badges -->
<p align="left">
  <a href="https://github.com/Swiss-Library-Service-Platform/slskey-backend/actions/workflows/pest_tests.yml"><img src="https://github.com/Swiss-Library-Service-Platform/slskey-backend/actions/workflows/pest_tests.yml/badge.svg" alt="Test Status"></a>
  <a href="https://github.com/Swiss-Library-Service-Platform/slskey-backend/actions/workflows/pest_tests.yml"><img src="https://raw.githubusercontent.com/Swiss-Library-Service-Platform/slskey-backend/coverage-badge/coverage.svg" alt="Coverage"></a>
  <a href="https://github.com/Swiss-Library-Service-Platform/slskey-backend/actions/workflows/security.yml"><img src="https://github.com/Swiss-Library-Service-Platform/slskey-backend/actions/workflows/security.yml/badge.svg" alt="Security Status"></a>
</p>

## Table of Contents

1. [Overview](#overview)
2. [Requirements](#requirements)
3. [Documentation](#documentation)
4. [License](#license)

## Overview

SLSKey is an authentication service originally designed to enable private users (non-affiliated individuals) of universities to access electronic resources that are typically restricted to institution members. Since then, it has expanded to serve institutions that don't operate their own identity provider, allowing them to use [Switch edu-ID](https://eduid.ch/) for authenticating all their users, from students to staff. The accompanying Alma Cloud App empowers librarians to manage SLSKey access rights directly within Alma, seamlessly communicating with this backend to streamline user authorization workflows.

This identity-based approach means users can access resources from anywhere, without needing VPN access. Institutions maintain full control over user access through a web-based management interface, while benefiting from Switch edu-ID's robust authentication infrastructure.

## Documentation

- [Service Documentation](https://slsp.atlassian.net/wiki/spaces/slsporgserv/pages/1042415617) - Detailed system documentation on SLSPhere (Confluence)
- [Developer Notes](DEVELOPER_NOTES.md) - Comprehensive development documentation

## Alma Cloud App

For the SLSKey Alma Cloud App repository, see [here](https://github.com/Swiss-Library-Service-Platform/slskey-cloud-app).

## License

Copyright © Swiss Library Service Platform (SLSP)
