<?php
/**
 * Created by PhpStorm.
 * User: Gigy
 * Date: 09/11/2018
 * Time: 17:31
 */

namespace App\Service;


use App\Entity\Category;
use App\Entity\Document;
use App\Entity\Download;
use App\Entity\Event;
use App\Entity\Post;
use App\Entity\School;
use App\Entity\SchoolOfTheDay;
use App\Entity\SentMail;
use App\Entity\User;
use App\Entity\View;
use App\Entity\Visit;
use App\Model\MailMessage;
use App\Repository\AvatarRepository;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
use App\Repository\CoverRepository;
use App\Repository\EventRepository;
use App\Repository\LogoRepository;
use App\Repository\ParameterRepository;
use App\Repository\PostRepository;
use App\Repository\SchoolContactRepository;
use App\Repository\SchoolOfTheDayRepository;
use App\Repository\SchoolRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Templating\EngineInterface;

class PlatformService
{
    /**
     * @var EngineInterface
     */
    private $templating;

    public function __construct(
        SchoolRepository $schoolRepository,
        ParameterRepository $parameterRepository,
        AvatarRepository $avatarRepository,
        PostRepository $postRepository,
        EventRepository $eventRepository,
        EntityManagerInterface $em,
        \Swift_Mailer $mailer,
        EngineInterface $templating,
        TokenStorageInterface $token,
        RequestStack $requestStack
    )
    {
        $this->schoolRepository = $schoolRepository;
        $this->parameterRepository = $parameterRepository;
        $this->avatarRepository = $avatarRepository;
        $this->postRepository = $postRepository;
        $this->eventRepository = $eventRepository;
        $this->em = $em;
        $this->templating = $templating;
        $this->mailer = $mailer;
        $this->token = $token;
        $this->requestStack = $requestStack;
    }

    public function substrSpace($string, $length) {
        if(strlen($string)>$length){
            $string = substr($string, 0, $length);
            $posLastSpace = strrpos($string, " ");
            $string = substr($string, 0, $posLastSpace);
            $string = $string."...";
        }
        return $string;
    }

    function sluggify($str) {
        $before = array(
            '/[^a-z0-9\s]/',
            array(
                '/\s/',
                '/--+/',
                '/---+/'),
            '/\&/'
        );

        $after = '-';

        $str = str_replace("’", "-", $str);
        $str = str_replace("–", "-", $str);
        $str = str_replace("«", "-", $str);
        $str = str_replace("»", "-", $str);
        $str = preg_replace($before[2], $after, $str);
        $str = htmlentities($str, ENT_NOQUOTES, 'utf-8');

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'

        $str = strtolower($str);
        $str = preg_replace($before[0], $after, $str);
        $str = trim($str);
        $str = preg_replace($before[1], $after, $str);
        $str = trim($str, '-');

        //get 240 characters
        $str = substr($str, 0, 240);  // bcd

        return $str;
    }

    function getSlug($slug, $entity) {
        $slug = $this->sluggify($slug);

        $slugtmp = $slug;
        $notSlugs = array(
            "school",
            "blog",
            "event",
            "events",
            "advert",
            "forum",
            "about",
            "team",
            "legal-notice",
            "contact",
            "newsletter",
            "categories",
            "category",
            "user",
            "admin",
            "logout",
            "login",
            "register",
            "archive",
            "archives",
            "tag",
            "tags",
        );
        $isSluggable = true;
        $i = 2;
        do {
            if ($entity instanceof School){
                $entitytmp = $this->schoolRepository->findOneBy(array(
                    'slug' => $slugtmp
                ));
            }
            elseif ($entity instanceof Post){
                $entitytmp = $this->postRepository->findOneBy(array(
                    'slug' => $slugtmp
                ));
            }
            elseif ($entity instanceof Event){
                $entitytmp = $this->eventRepository->findOneBy(array(
                    'slug' => $slugtmp
                ));
            }
            if(($entitytmp && $entitytmp->getId() != $entity->getId()) || in_array($slugtmp, $notSlugs)){
                //if($entitytmp || in_array($slugtmp, $notSlugs)){
                $slugtmp = $slug."-".$i;
                $i++;
            }
            else{
                $isSluggable = false;
            }
        }
        while ($isSluggable);
        $slug = $slugtmp;

        return $slug;
    }

    public function imagineFilter($path, $size){
        $content = $this->templating->render('imagine_filter.html.twig', array(
            'path' => $path,
            'size' => $size,
        ));
        return $content;
    }

    public function registerView($entity) {

        $view = new View();
        if ($entity instanceof School){
            $view->setSchool($entity);
        }else if($entity instanceof Post){
            $view->setPost($entity);
        }

        $user = $this->token->getToken()->getUser();
        if($user instanceof User){
            $view->setUser($user);
        }

        $request = $this->requestStack->getCurrentRequest();
        $clientIp = $request->getClientIp();
        $view->setIp($clientIp);
        $view->setDate(new \DateTime());

        $this->em->persist($view);
        $this->em->flush();

        return $view;
    }

    public function registerVisit() {

        $visit = new Visit();

        $date = new \DateTime();
        $user = $this->token->getToken()->getUser();
        if($user instanceof User){
            $user->setLastActivity($date);
            $this->em->persist($user);
            $visit->setUser($user);
        }

        $request = $this->requestStack->getCurrentRequest();
        $relativeUrl = $request->getRequestUri();
        $visit->setUrl($relativeUrl);

        $clientIp = $request->getClientIp();
        $visit->setIp($clientIp);
        $visit->setDate($date);

        $ajax = false;
        if ($request->isXmlHttpRequest()){
            $ajax = true;
        }
        $visit->setAjax($ajax);

        $this->em->persist($visit);
        $this->em->flush();

        return $visit;
    }

    public function registerDownload($entity, $user = null, $request) {

        $download = new Download();
        if ($entity instanceof Document){
            $download->setDocument($entity);
        }

        $download->setUser($user);
        $clientIp = $request->getClientIp();
        $download->setIp($clientIp);
        $download->setDate(new \DateTime());

        $this->em->persist($download);
        $this->em->flush();

        return $download;
    }

    /*
    *generate random string
    */
    function generateRandomString($length = 32) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function fileIcon($filename) {
        $position = strripos($filename,".");
        $extension = substr($filename,$position+1,strlen($filename));
        $fileicon = "file";
        switch ($extension) {
            case "jpg":
                $fileicon = "jpg";
                break;
            case "jpeg":
                $fileicon = "jpg";
                break;
            case "png":
                $fileicon = "png";
                break;
            case "pdf":
                $fileicon = "pdf";
                break;
            case "zip":
                $fileicon = "zip";
                break;
            case "rar":
                $fileicon = "rar";
                break;
        }
        return $fileicon;
    }

    public function getDate($dateString, $format = 'd/m/y') {
        $year = 0;
        $month = 0;
        $day = 0;
        $hour = 0;
        $minute = 0;
        $second = 0;

        $isDate = false;
        $isTime = false;
        $date = null;

        $dateString = trim(preg_replace('!\s+!', ' ', $dateString));
        switch ($format) {
            case 'd/m/y':
                $dateArray = explode("/", $dateString);
                if(count($dateArray) == 3){
                    $year = intval($dateArray[2]);
                    $month = intval($dateArray[1]);
                    $day = intval($dateArray[0]);
                }
                break;
            case 'd-m-y':
                $dateArray = explode("-", $dateString);
                if(count($dateArray) == 3){
                    $year = intval($dateArray[2]);
                    $month = intval($dateArray[1]);
                    $day = intval($dateArray[0]);
                }
                break;
            case 'd/m/y h:i':
                $dateTimeArray = explode(" ", $dateString);
                if(count($dateTimeArray) == 2){
                    //date
                    $dateArray = explode("/", $dateTimeArray[0]);
                    if(count($dateArray) == 3){
                        $year = intval($dateArray[2]);
                        $month = intval($dateArray[1]);
                        $day = intval($dateArray[0]);
                    }
                    //time
                    $timeArray = explode(":", $dateTimeArray[1]);
                    if(count($timeArray) == 2){
                        $hour = intval($timeArray[0]);
                        $minute = intval($timeArray[1]);
                    }
                }
                break;
            case 'd-m-y h:i':
                $dateTimeArray = explode(" ", $dateString);
                if(count($dateTimeArray) == 2){
                    //date
                    $dateArray = explode("-", $dateTimeArray[0]);
                    if(count($dateArray) == 3){
                        $year = intval($dateArray[2]);
                        $month = intval($dateArray[1]);
                        $day = intval($dateArray[0]);
                    }
                    //time
                    $timeArray = explode(":", $dateTimeArray[1]);
                    if(count($timeArray) == 2){
                        $hour = intval($timeArray[0]);
                        $minute = intval($timeArray[1]);
                    }
                }
                break;
        }

        //checking date
        if($year >= 1000 && $year <= 9999){
            if($month == 1 || $month == 3 || $month == 5 || $month == 7 || $month == 8 || $month == 10 || $month == 12 ){
                if($day >= 1 && $day <= 31 ){
                    $isDate = true;
                }
            }elseif($month == 4 || $month == 6 || $month == 9 || $month == 11 ){
                if($day >= 1 && $day <= 30 ){
                    $isDate = true;
                }
            }elseif($month == 2 ){
                if($day >= 1 && $day <= 28 ){
                    $isDate = true;
                }elseif($day == 29 ){
                    if (($year % 4) == 0){
                        $isDate = true;
                    }
                }
            }
        }

        //checking time
        if($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59){
            $isTime = true;
        }

        if($isDate && $isTime){
            $date = new \DateTime($year."-".$month."-".$day." ".$hour.":".$minute.":".$second);
        }

        return $date;
    }

    public function email(MailMessage $message) {
        $body = $message->getBody();
        if($message->getWrap() && trim($message->getWrap()) != ""){
            $body = $this->templating->render($message->getWrap().".html.twig", array(
                'content' => $message->getBody(),
            ));
        }

        $subject = $message->getSubject();
        $from = $message->getFrom();
        $to = $message->getTo();
        $swiftMessage = (new \Swift_Message($subject))
            ->setFrom($from)
            ->setTo($to)
            ->setBody($body,
                "text/html"
            )
        ;

        $this->mailer->send($swiftMessage);

        $sentMail = new SentMail();
        $sentMail->setDate(new \DateTime());
        $sentMail->setSubject($subject);
        $sentMail->setBody($body);
        $sentMail->setSender($from);
        $sentMail->setRecipient($to);

        $this->em->persist($sentMail);
        $this->em->flush();

    }
}