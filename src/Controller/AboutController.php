<?php
namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ParameterRepository;
use App\Repository\UserTeamRepository;
use App\Repository\SchoolRepository;
use App\Service\PlatformService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class AboutController extends AbstractController {

    public function __construct(
        ParameterRepository $parameterRepository,
        PlatformService $platformService,
        UserTeamRepository $userTeamRepository,
        SchoolRepository $schoolRepository,
        ObjectManager $em,
        \Swift_Mailer $mailer
    )
    {
        $this->parameterRepository = $parameterRepository;
        $this->platformService = $platformService;
        $this->userTeamRepository = $userTeamRepository;
        $this->schoolRepository = $schoolRepository;
        $this->mailer = $mailer;
        $this->em = $em;

        $this->platformService->registerVisit();
    }

    public function index()
    {
        return $this->render('platform/about.html.twig');
    }

    public function team()
    {
        $users = $this->userTeamRepository->findOrderBy('position', 'ASC', true);
        return $this->render('platform/team.html.twig', array(
            'users' => $users
        ));
    }

    public function notice()
    {
        return $this->render('platform/notice.html.twig');
    }

    public function sitemapXml(Request $request)
    {
        $response = new Response();

        $hostname = $request->getSchemeAndHttpHost();
        $urls = array();
        
        // add static urls
        $urls[] = array('loc' => $this->get('router')->generate('platform_home'));
        $urls[] = array('loc' => $this->get('router')->generate('platform_about'));
        $urls[] = array('loc' => $this->get('router')->generate('platform_contact'));
        $urls[] = array('loc' => $this->get('router')->generate('platform_newsletter'));

        $urls[] = array('loc' => $this->get('router')->generate('user_login'));
        $urls[] = array('loc' => $this->get('router')->generate('user_register'));

        $urls[] = array('loc' => $this->get('router')->generate('blog_manager_doadd_post'));
        $urls[] = array('loc' => $this->get('router')->generate('event_manager_doadd_event'));

        $urls[] = array('loc' => $this->get('router')->generate('blog'));

        $urls[] = array('loc' => $this->get('router')->generate('event'));

        $urls[] = array('loc' => $this->get('router')->generate('school_categories'));
        $urls[] = array('loc' => $this->get('router')->generate('school_map'));
        $urls[] = array('loc' => $this->get('router')->generate('school_of_the_day'));
        $urls[] = array('loc' => $this->get('router')->generate('school_home'));
        $schools = $this->schoolRepository->findBy(array(
            "published" => true
        ));

        foreach($schools as $school){
            $urls[] = array('loc' => $this->get('router')->generate('school_view', array(
                'slug' => $school->getSlug(),
            )));
        }

        $response = $this->render('platform/sitemap_xml.html.twig', array(
            'urls' => $urls,
            'hostname' => $hostname,
        ));

        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }
}