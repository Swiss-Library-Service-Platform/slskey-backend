<template>
    <AppLayout :modePublic="true" title="Content Providers" :breadCrumbs="[
        {
            name: '',
        },
    ]">
        <div class="w-full flex flex-row align-center py-8 px-10 mt-5 mb-10 bg-white rounded-sm shadow-md">
            <img class="h-40 w-auto" src="/images/slskey_logo_full_black.png"/>
            <div class="ml-20 flex flex-col">
                <div class="text-3xl">SLSKey for Content Providers</div>
                <div class="mt-5 flex flex-col">
                    Thank you for your interest in becoming a content provider for SLSKey. <br>
                    SLSKey allows users of libraries in the swisscovery network to access your services. <br>
                    We have summarised all the important information for you on this website. <br>
                </div>
            </div>
        </div>
        <div class="w-full align-center py-8 mt-5 mb-10 bg-white rounded-sm  shadow-md">
            <!-- General Information -->
            <div class="flex flex-col">
                <div class="px-10 text-3xl">Index</div>
                <div class="px-10 mt-5 flex flex-col ">
                    <div class="font-bold mb-2">General Information</div>
                    <!-- List of links with bullet points -->
                    <ul class="list-disc pl-4 underline">
                        <li>
                            <a href="#slsp">SLSP: Swiss Library Service Platform</a>
                        </li>
                        <li>
                            <a href="#slskey">SLSKey</a>
                        </li>
                        <li>
                            <a href="#eduid">Switch edu-ID</a>
                        </li>
                    </ul>
                </div>
                <div class="px-10 mt-5 flex flex-col">
                    <div class="font-bold mb-2">Technical Details</div>
                    <!-- List of links with bullet points -->
                    <ul class="list-disc pl-4 underline">
                        <li>
                            <a href="#concept">Technical Concept</a>
                        </li>
                        <li>
                            <a href="#requirements">Technical Requirements</a>
                        </li>
                    </ul>
                </div>
                <div class="px-10 mt-5 flex flex-col">
                    <div class="font-bold mb-2">Help</div>
                    <!-- List of links with bullet points -->
                    <ul class="list-disc pl-4 underline">
                        <li>
                            <a href="#help">Contact Us</a>
                        </li>
                    </ul>
                </div>
            </Div>
        </div>

        <div class="w-full align-center py-8 mt-5 mb-10 bg-white rounded-sm shadow-md">
            <!-- General Information -->
            <div class="flex flex-col">
                <div class="px-10 text-3xl ">General Information</div>
                <div id="slsp" class="px-10 py-5 flex flex-col border-b">
                    <div class="text-xl underline mb-2">SLSP: Swiss Library Service Platform</div>
                    <div>
                        The Swiss Library Service Platform (SLSP) is a non-profit organisation founded in 2015 as a
                        project of as a project of 15 Swiss libraries. <br>
                        Since 2020, it has been working with these libraries to operate the national library platform
                        "swisscovery", which brings together scientific information from currently 500
                        libraries and makes it accessible to users. <br>
                        These 500 libraries also include many public libraries (e.g. cantonal libraries) which also also
                        make their resources available to the public.<br>
                        <br>
                        For more information on the SLSP, please visit <a class="underline"
                            href="https://www.slsp.ch">www.slsp.ch</a>.
                    </div>
                </div>
                <div id="slskey" class="px-10 py-5 flex flex-col border-b">
                    <div class="text-xl underline mb-2">SLSKey</div>
                    <div>
                        SLSKey is a service provided by SLSP to libraries.<br>
                        It enables library users to access E-Resources via a Single Sign-On.<br>
                        <br>
                        Authentication is done via Switch edu-ID.
                    </div>
                </div>
                <div id="eduid" class="px-10 py-5 flex flex-col">
                    <div class="text-xl underline mb-2">Switch edu-ID</div>
                    <div>
                        Switch edu-ID is a personal and universally usable digital identity in the higher education
                        sector and beyond.<br>
                        Swiss universities use Switch edu-ID as an electronic identity solution for students, lecturers
                        and staff.<br>
                        However, walk-in users without a permanent affiliation to an institution can also create and
                        benefit from a Switch edu-ID user account.<br>
                        There are (as of June 2023) nearly 400,000 Switch edu-ID user accounts created for the use of
                        swisscovery. <br>
                        <br>
                        For authentication via Switch edu-ID, one of the two authentication protocols widely used in the
                        university environment, SAML2.0 or OpenID Connect, is used.<br>
                        <br>
                        For more informations about the edu-ID, please visit <a class="underline"
                            href="https://www.switch.ch/edu-id">https://www.switch.ch/de/edu-id</a>.
                    </div>
                </div>
            </div>
        </div>
        <div class="w-full align-center py-8 mt-5 mb-10 bg-white rounded-sm shadow-md">

            <!-- Technical Details -->
            <div class="flex flex-col">
                <div class="px-10 text-3xl">Technical Details</div>
                <div id="concept" class="px-10 py-5 flex flex-col border-b">
                    <div class="text-xl underline mb-2">Technical Concept</div>
                    <div>
                        Switch edu-ID via SAML2.0 or OpenID Connect is used to authenticate SLSKey users to your
                        service.<br>
                        <br>
                        All users in the discovery service select the Switch edu-ID as their Identity Provider,
                        regardless of which library they are accessing.<br>
                        <br>
                        Authorisation is via the SAML2.0 attribute or the OpenID Connect claim
                        <a class="underline"
                            href="https://help.switch.ch/aai/support/documents/attributes/edupersonentitlement/">eduPersonEntitlement</a>.<br>
                        of the Switch edu-ID identity. The value of this attribute is automatically set by
                        SLSKey for authorised persons. As a provider of electronic resources, you must therefore check
                        that
                        whether the agreed value is present after users have logged in to the relevant service using the
                        edu-ID.<br>
                        <br>
                        Technical concept of SLSKey: <br>
                        <div class="flex justify-center mt-5">
                            <img src="/images/concept.png" class="w-auto" />
                        </div>

                    </div>
                </div>
                <div id="requirements" class="px-10 py-5 flex flex-col">
                    <div class="text-xl underline mb-2">Technical Requirements</div>
                    <div>
                        The following requirements must be met to enable access to a service via SLSKey:
                        <div class="flex flex-col mt-10">
                            <div class="font-bold">
                                1. SWITCHaai federation partner or member of eduGAIN
                            </div>
                            <div class="pl-5 mt-2">
                                To enable access to your services via SLSKey, you must either become a SWITCHaai
                                federation partner or the service must be part of <a href="https://edugain.org/"
                                    class="underline">eduGAIN</a> via another identity federation.
                                As a SWITCHaai federation partner, you can make your services accessible to users with a
                                SWITCHaai account.
                                Find out more about becoming a Switch federation partner <a class="underline"
                                    href="https://www.switch.ch/aai/join/partners/">here</a>.
                                If your service is already accessible via eduGAIN, you do not need to become a
                                federation partner.
                            </div>
                        </div>
                        <div class="flex flex-col mt-10">
                            <div class="font-bold">
                                2. Support of SAML2.0 or OpenID Connect as authentication protocol
                            </div>
                            <div class="pl-5 mt-2">
                                To enable access to your services via SLSKey, you must support either the SAML (Security
                                Assertion Markup Language) protocol or the OpenID Connect protocol as an authentication
                                protocol.
                                Your service should therefore either act as a SAML Service Provider (SP) or provide an
                                OpenID Connect Relying Party (RP). <br>
                                
                            </div>
                        </div>
                        <div class="flex flex-col mt-10">
                            <div class="font-bold">
                                3. Implement Authentication via Switch edu-ID
                            </div>
                            <div class="pl-5 mt-2">
                                The Identity Provider for the authentication of the users is the Switch edu-ID IdP.
                                <ul class="list-disc pl-8 mt-2">
                                        <li>
                                            SAML2.0: Entity-ID: https://eduid.ch/idp/shibboleth <br>
                                            Metadata: <a
                                                href="https://metadata.aai.switch.ch/entities/eduid"
                                                class="underline">Switch edu-ID Metadata</a>
                                        </li>
                                        <li>
                                            OpenID Connect Endpoint: https://login.eduid.ch/idp/profile/oidc/authorize <br>
                                            Well-Known Config: <a
                                                href="https://login.eduid.ch/.well-known/openid-configuration"
                                                class="underline">Switch edu-ID OpenID Connect</a>
                                        </li>
                                    </ul>
                                <br>
                                Ideally, the Identity Provider is added to the Discovery Service (WAYF) using a
                                display name specific to the library, e.g. «Library X via Switch edu-ID».
                                This makes it easier for users to select the correct Identity Provider instead of the generic name "Switch edu-ID".
                            </div>
                        </div>
                        <div class="flex flex-col mt-10">
                            <div class="font-bold">
                                4. Carrying out the Authorization
                            </div>
                            <div class="pl-5 mt-2">
                                To ensure that only authorized users can access your services, you must carry out
                                authorization.
                                <br>
                                <div class="font-bold mt-5">
                                    4a. Authorization based on user attribute "eduPersonEntitlement"
                                </div>
                                <div class="pl-5 mt-2">
                                    To ensure that only authorized users can access your services, you must carry out
                                    authorization and assignment to the institution using the "eduPersonEntitlement"
                                    value. The attribute is multi-valued and contains the entitlements of the user.

                                    <ul class="list-disc pl-8 mt-2">
                                        <li>
                                            SAML2.0: <a
                                                href="https://help.switch.ch/aai/support/documents/attributes/edupersonentitlement/"
                                                class="underline">Attribute "eduPersonEntitlement" (1.3.6.1.4.1.5923.1.1.1.7)</a>
                                        </li>
                                        <li>
                                            OpenID Connect: <a
                                                href="https://help.switch.ch/eduid/docs/services/openid-connect/scopes/#toc3"
                                                class="underline">Claim "eduPersonEntitlement"</a>
                                        </li>
                                    </ul>
                                    <br>
                                    <div class="pl-5 mt-2">
                                        To work properly, it is important that not only one fixed value (e.g. common-lib
                                        terms) can be checked, but that different values for different institutions can
                                        be
                                        read and interpreted (e.g. https://slsp.ch/entitlement/[provider]/[library]).
                                        The exact value of the eduPersonEntitlement that authorised users will have for
                                        your
                                        service will be communicated to you in advance by the institution.<br>
                                    </div>
                                </div>
                                <div class="font-bold mt-5">
                                    4b. (Optional): Support more than one IdPs per licence
                                </div>
                                <div class="pl-5 mt-2">
                                    This point is only necessary if the library for which the SLSKey access is to be set up has already configured access to your platform for its institutional users.
                                    In this case, your platform should be able to support more than one IdP Entity-ID per licence.
                                    Where licence terms allow, users authenticating via the Switch edu-ID IdP should be able to access the same content as users authenticating via the institutional IdP.                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col mt-10">
                            <div class="font-bold">
                                5. Support multiple different licence holders (libraries) using the same IdP (Switch edu-ID)
                            </div>
                            <div class="pl-5 mt-2">
                                Library users can be authorised by different libraries to access their resources via
                                SLSKey.
                                However, all library users select the same SAML Identity Provider or OpenID Connect
                                Provider ("Switch edu-ID") in the Discovery Service (WAYF).
                                For this reason, users cannot be assigned to a library by the Identity Provider's
                                entity ID.
                                If it is necessary to know which library the users are authorised by, the value of
                                'eduPersonEntitlement' must be evaluated.
                                This value is the same for all users of a library, but is different for users of
                                different libraries.
                                This might not be necessary if the library is the first to use the service. But as it is 
                                possible that other libraries will use the service in the future, it is recommended to
                                support this from the beginning.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-full align-center py-8 mt-5 mb-10 bg-white rounded-sm shadow-md">
            <div class="flex flex-col">
                <div class="px-10 text-3xl ">Help</div>
                <div id="help" class="px-10 py-5 flex flex-col">
                    <div class="text-xl underline mb-2">Contact us</div>
                    <div>
                        If you have any further questions or need additional support, please contact the SLSKey team:
                        <a href="mailto:info@slsp.ch" class="underline">info@slsp.ch</a>.
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import Icon from '@/Shared/Icon.vue';

// register globally
export default {
    components: {
        AppLayout,
        Icon
    },
    props: {

    },
    data() {
        return {};
    },
    methods: {
        formatDate(date) {
            return this.$moment(date).format("ll");
        },
    },
};
</script>