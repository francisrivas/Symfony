<?php

namespace Symfony\Component\WebLink\Enum;

enum LinkRelations: string
{
    case ABOUT = 'about';
    case ACL = 'acl';
    case ALTERNATE = 'alternate';
    case AMPHTML = 'amphtml';
    case APPENDIX = 'appendix';
    case APPLE_TOUCH_ICON = 'apple-touch-icon';
    case APPLE_TOUCH_STARTUP_IMAGE = 'apple-touch-startup-image';
    case ARCHIVES = 'archives';
    case AUTHOR = 'author';
    case BLOCKED_BY = 'blocked-by';
    case BOOKMARK = 'bookmark';
    case CANONICAL = 'canonical';
    case CHAPTER = 'chapter';
    case CITE_AS = 'cite-as';
    case COLLECTION = 'collection';
    case CONTENTS = 'contents';
    case CONVERTEDFROM = 'convertedfrom';
    case COPYRIGHT = 'copyright';
    case CREATE_FORM = 'create-form';
    case CURRENT = 'current';
    case DESCRIBEDBY = 'describedby';
    case DESCRIBES = 'describes';
    case DISCLOSURE = 'disclosure';
    case DNS_PREFETCH = 'dns-prefetch';
    case DUPLICATE = 'duplicate';
    case EDIT = 'edit';
    case EDIT_FORM = 'edit-form';
    case EDIT_MEDIA = 'edit-media';
    case ENCLOSURE = 'enclosure';
    case EXTERNAL = 'external';
    case FIRST = 'first';
    case GLOSSARY = 'glossary';
    case HELP = 'help';
    case HOSTS = 'hosts';
    case HUB = 'hub';
    case ICON = 'icon';
    case INDEX = 'index';
    case INTERVALAFTER = 'intervalafter';
    case INTERVALBEFORE = 'intervalbefore';
    case INTERVALCONTAINS = 'intervalcontains';
    case INTERVALDISJOINT = 'intervaldisjoint';
    case INTERVALDURING = 'intervalduring';
    case INTERVALEQUALS = 'intervalequals';
    case INTERVALFINISHEDBY = 'intervalfinishedby';
    case INTERVALFINISHES = 'intervalfinishes';
    case INTERVALIN = 'intervalin';
    case INTERVALMEETS = 'intervalmeets';
    case INTERVALMETBY = 'intervalmetby';
    case INTERVALOVERLAPPEDBY = 'intervaloverlappedby';
    case INTERVALOVERLAPS = 'intervaloverlaps';
    case INTERVALSTARTEDBY = 'intervalstartedby';
    case INTERVALSTARTS = 'intervalstarts';
    case ITEM = 'item';
    case LAST = 'last';
    case LATEST_VERSION = 'latest-version';
    case LICENSE = 'license';
    case LINKSET = 'linkset';
    case LRDD = 'lrdd';
    case MANIFEST = 'manifest';
    case MASK_ICON = 'mask-icon';
    case MEDIA_FEED = 'media-feed';
    case MEMENTO = 'memento';
    case MERCURE = 'mercure';
    case MICROPUB = 'micropub';
    case MODULEPRELOAD = 'modulepreload';
    case MONITOR = 'monitor';
    case MONITOR_GROUP = 'monitor-group';
    case NEXT = 'next';
    case NEXT_ARCHIVE = 'next-archive';
    case NOFOLLOW = 'nofollow';
    case NOOPENER = 'noopener';
    case NOREFERRER = 'noreferrer';
    case OPENER = 'opener';
    case OPENID2_LOCAL_ID = 'openid2.local_id';
    case OPENID2_PROVIDER = 'openid2.provider';
    case ORIGINAL = 'original';
    case P3PV1 = 'p3pv1';
    case PAYMENT = 'payment';
    case PINGBACK = 'pingback';
    case PRECONNECT = 'preconnect';
    case PREDECESSOR_VERSION = 'predecessor-version';
    case PREFETCH = 'prefetch';
    case PRELOAD = 'preload';
    case PRERENDER = 'prerender';
    case PREV = 'prev';
    case PREVIEW = 'preview';
    case PREVIOUS = 'previous';
    case PREV_ARCHIVE = 'prev-archive';
    case PRIVACY_POLICY = 'privacy-policy';
    case PROFILE = 'profile';
    case PUBLICATION = 'publication';
    case RELATED = 'related';
    case RESTCONF = 'restconf';
    case REPLIES = 'replies';
    case RULEINPUT = 'ruleinput';
    case SEARCH = 'search';
    case SECTION = 'section';
    case SELF = 'self';
    case SERVICE = 'service';
    case SERVICE_DESC = 'service-desc';
    case SERVICE_DOC = 'service-doc';
    case SERVICE_META = 'service-meta';
    case SIPTRUNKINGCAPABILITY = 'siptrunkingcapability';
    case SPONSORED = 'sponsored';
    case START = 'start';
    case STATUS = 'status';
    case STYLESHEET = 'stylesheet';
    case SUBSECTION = 'subsection';
    case SUCCESSOR_VERSION = 'successor-version';
    case SUNSET = 'sunset';
    case TAG = 'tag';
    case TERMS_OF_SERVICE = 'terms-of-service';
    case TIMEGATE = 'timegate';
    case TIMEMAP = 'timemap';
    case TYPE = 'type';
    case UGC = 'ugc';
    case UP = 'up';
    case VERSION_HISTORY = 'version-history';
    case VIA = 'via';
    case WEBMENTION = 'webmention';
    case WORKING_COPY = 'working-copy';
    case WORKING_COPY_OF = 'working-copy-of';
}
