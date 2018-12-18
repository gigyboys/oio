<?php
/**
 * Created by PhpStorm.
 * User: Gigy
 * Date: 09/11/2018
 * Time: 17:31
 */

namespace App\Service;


use App\Entity\Category;
use App\Entity\DocumentAuthorization;
use App\Entity\School;
use App\Entity\SchoolOfTheDay;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
use App\Repository\CoverRepository;
use App\Repository\DocumentAuthorizationRepository;
use App\Repository\DocumentRepository;
use App\Repository\EvaluationRepository;
use App\Repository\LogoRepository;
use App\Repository\ParameterRepository;
use App\Repository\SchoolContactRepository;
use App\Repository\SchoolOfTheDayRepository;
use App\Repository\SchoolRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\TypeSchoolRepository;
use App\Repository\ViewRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class SchoolService
{
    public function __construct(
        SchoolRepository $schoolRepository,
        ParameterRepository $parameterRepository,
        CategoryRepository $categoryRepository,
        CategorySchoolRepository $categorySchoolRepository,
        SchoolOfTheDayRepository $schoolOfTheDayRepository,
        LogoRepository $logoRepository,
        CoverRepository $coverRepository,
        EvaluationRepository $evaluationRepository,
        SubscriptionRepository $subscriptionRepository,
        DocumentRepository $documentRepository,
        DocumentAuthorizationRepository $documentAuthorizationRepository,
        ViewRepository $viewRepository,
        EntityManagerInterface $em
    )
    {
        $this->schoolRepository = $schoolRepository;
        $this->parameterRepository = $parameterRepository;
        $this->categoryRepository = $categoryRepository;
        $this->categorySchoolRepository = $categorySchoolRepository;
        $this->schoolOfTheDayRepository = $schoolOfTheDayRepository;
        $this->logoRepository = $logoRepository;
        $this->coverRepository = $coverRepository;
        $this->evaluationRepository = $evaluationRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->documentRepository = $documentRepository;
        $this->documentAuthorizationRepository = $documentAuthorizationRepository;
        $this->viewRepository = $viewRepository;
        $this->em = $em;
    }

    public function getCategoriesWithPublishedSchool($limit)
    {

        $categoryArray = array();
        $categories = $this->categoryRepository->findAllOrderByName("ASC");

        foreach($categories as $category){

            $categorySchools = $this->categorySchoolRepository->findBy(array(
                'category' => $category,
            ));
            $isCategory = false;
            foreach($categorySchools as $categorySchool){
                $school = $categorySchool->getSchool();
                if($school->getPublished()){
                    $isCategory = true;
                }
            }
            if($isCategory){
                array_push($categoryArray, $category);
            }
        }

        if($limit == 0){
            return $categoryArray;
        }else{
            $categoriesLimit = array();
            if(count($categoryArray) < $limit){
                $end = count($categoryArray);
            }else{
                $end = $limit;
            }

            for ($i=0; $i<$end; $i++) {
                array_push($categoriesLimit, $categoryArray[$i]);
            }

            return $categoriesLimit;
        }
    }

    public function getAllSchoolByCategory(Category $category, $published = null) {

        $schools = array();
        $categorySchools = $this->categorySchoolRepository->findBy(array(
            'category' => $category,
        ));

        foreach($categorySchools as $categorySchool){
            $school = $categorySchool->getSchool();
            if($published == null){
                array_push($schools, $school);
            }else{
                if($school->getPublished() == $published){
                    array_push($schools, $school);
                }
            }
        }

        return $schools;
    }

    public function getCurrentSchoolByCategory(Category $category) {
        $categorySchool = $this->categorySchoolRepository->findOneBy(array(
            'category' => $category,
            'current' => true,
        ));

        if($categorySchool && $categorySchool->getSchool()->getPublished()){
            return $categorySchool->getSchool();
        }else{
            $schools = array();
            $categorySchools = $this->categorySchoolRepository->findBy(array(
                'category' => $category,
            ));

            foreach($categorySchools as $categorySchool){
                $school = $categorySchool->getSchool();
                if($school->getPublished() == true){
                    array_push($schools, $school);
                }
            }
            if($schools){
                $index = array_rand($schools, 1);
                return $schools[$index];
            }

            return null;
        }
    }

    public function getSchoolByCategoryOffsetLimit(Category $category, $offset, $limit, $published = null, $field = 'position', $order = 'ASC') {
        $allSchools = array();
        $schools = array();
        $categorySchools = $this->categorySchoolRepository->findBy(array(
            'category' => $category,
        ));

        foreach($categorySchools as $categorySchool){
            $school = $categorySchool->getSchool();
            if($published != null){
                if($school->getPublished() == $published){
                    array_push($allSchools, $school);
                }
            }
        }

        if($order == 'ASC'){
            usort($allSchools, function($a, $b) {
                    return $a->getPosition() - $b->getPosition();
                });
        }elseif ($order == 'DESC'){
            usort($allSchools, function($a, $b) {
                return $b->getPosition() - $a->getPosition();
            });
        }

        $start = $offset;
        if(count($allSchools) < $offset+$limit){
            $end = count($allSchools);
        }else{
            $end = $offset+$limit;
        }

        for ($i=$offset; $i<$end; $i++) {
            array_push($schools, $allSchools[$i]);
        }

        return $schools;
    }

    public function getLastAddedSchools($limit) {
        $schoolTemps = $this->schoolRepository->getLastAddedSchools($limit);
        $schools = array();

        for ($i=count($schoolTemps)-1; $i>=0; $i--) {
            array_push($schools, $schoolTemps[$i]);
        }

        return $schools;
    }

    public function getSchoolOfTheDay() {
        $date = new \DateTime();
        $school = null;

        $schoolOfTheDay = $this->schoolOfTheDayRepository->findOneBy(array(
            'day' => $date,
            'current' => true,
        ));

        if($schoolOfTheDay && $schoolOfTheDay->getSchool()->getPublished()){
            $school = $schoolOfTheDay->getSchool();
            return $school;
        }else{
            $schoolOfTheDays = $this->schoolOfTheDayRepository->findBy(array(
                'day' => $date
            ));

            foreach ($schoolOfTheDays as $schoolOfTheDay) {
                $schoolOfTheDay->setCurrent(false);
            }

            $schoolOfTheDay = new SchoolOfTheDay();
            $schoolOfTheDay->setDate($date);
            $schoolOfTheDay->setDay($date);
            $schoolOfTheDay->setCurrent(true);

            $schools = $this->schoolRepository->findBy(array(
                'published' => true,
            ));
            $schoolsArray = array();
            foreach($schools as $school){
                array_push($schoolsArray, $school);
            }

            if($schoolsArray){
                $index = array_rand($schoolsArray, 1);
                $school = $schoolsArray[$index];
                $schoolOfTheDay->setSchool($school);
                $this->em->persist($schoolOfTheDay);
                $this->em->flush();
            }
            return $school;
        }
    }

    public function lastEvaluatedSchool() {
        $evaluation = $this->evaluationRepository->getLastEvaluation();
        if($evaluation){
            return $evaluation->getSchool();
        }
        return null;
    }

    public function mostVisitedSchool() {
        $school = null;
        $schools = $this->schoolRepository->findBy(array(
            'published' => true,
        ));
        $viewNumber = 0;
        foreach($schools as $schoolTemp){
            $views = $this->viewRepository->findBy(array(
                'school' => $schoolTemp,
            ));
            if($viewNumber < count($views)){
                $viewNumber = count($views);
                $school = $schoolTemp;
            }
        }
        return $school;
    }

    public function getCategoriesBySchool(School $school, $limit, $shuffle) {
        $categories = array();
        $categorySchools = $this->categorySchoolRepository->findBy(array(
            'school' => $school,
        ));

        foreach($categorySchools as $categorySchool){
            $category = $categorySchool->getCategory();
            array_push($categories, $category);
        }

        if($shuffle){
            shuffle($categories);
        }

        if($limit == 0){
            return $categories;
        }else{
            $categoriesLimit = array();
            if(count($categories) < $limit){
                $end = count($categories);
            }else{
                $end = $limit;
            }

            for ($i=0; $i<$end; $i++) {
                array_push($categoriesLimit, $categories[$i]);
            }

            return $categoriesLimit;
        }
    }

    public function getLogoPath(School $school) {
        $logo = $this->logoRepository->findOneBy(array(
            'school' => $school,
            'current' => true,
        ));

        if($logo){
            return 'uploads/images/school/logo/'.$logo->getPath();
        }
        else{
            return 'default/images/school/logo/default.jpeg';
        }
    }

    public function getCoverPath(School $school) {
        $cover = $this->coverRepository->findOneBy(array(
            'school' => $school,
            'current' => true,
        ));

        if($cover){
            return 'uploads/images/school/cover/'.$cover->getPath();
        }
        else{
            return 'default/images/school/cover/default.jpeg';
        }
    }

    public function isCategorySchool(School $school, Category $category) {
        $categorySchool = $this->categorySchoolRepository->findOneBy(array(
            'school' => $school,
            'category' => $category,
        ));

        if($categorySchool){
            $isCategory = true;
        }else{
            $isCategory = false;
        }

        return $isCategory;
    }

    public function getType(School $school) {
        return $school->getType();
    }

    public function getRelatedSchools(School $school, $limit) {
        $schoolIds = array();
        $schools = array();
        $categories = array();
        $categorySchools = $this->categorySchoolRepository->findBy(array(
            'school' => $school,
        ));

        foreach($categorySchools as $categorySchool){
            $category = $categorySchool->getCategory();
            array_push($categories, $category);
            $schoolTemps = $this->getAllSchoolByCategory($category, true);
            foreach($schoolTemps as $schoolTemp){
                if (!in_array($schoolTemp->getId(), $schoolIds) && $schoolTemp->getId() != $school->getId()) {
                    array_push($schoolIds, $schoolTemp->getId());
                }
            }
        }

        shuffle($schoolIds);
        $start = 0;
        if(count($schoolIds) < $start+$limit){
            $end = count($schoolIds);
        }else{
            $end = $start+$limit;
        }

        for ($i=$start; $i<$end; $i++) {
            $school = $this->schoolRepository->find($schoolIds[$i]);
            array_push($schools, $school);
        }

        return $schools;
    }

    public function getEvaluatedSchools($limit) {
        $schools = array();
        $schoolTemps = $this->schoolRepository->findBy(array(
            'published' => true,
        ));

        foreach($schoolTemps as $school){
            $evaluations = $this->evaluationRepository->findBy(array(
                'school' => $school,
                'current' => true,
            ));
            if($evaluations){
                array_push($schools, $school);
            }
        }

        if($limit == 0){
            return $schools;
        }else{
            $schoolsLimit = array();
            if(count($schools) < $limit){
                $end = count($schools);
            }else{
                $end = $limit;
            }

            for ($i=0; $i<$end; $i++) {
                array_push($schoolsLimit, $schools[$i]);
            }

            return $schoolsLimit;
        }
    }

    public function getAllEvaluations() {
        $evaluations = array();
        $evaluationTemps = $this->evaluationRepository->findBy(array(
            'current' => true,
        ));

        foreach($evaluationTemps as $evaluation){
            if($evaluation->getSchool()->getPublished()){
                array_push($evaluations, $evaluation);
            }
        }

        return $evaluations;
    }

    public function getNextSchool(School $school) {
        $nextSchool = $this->schoolRepository->findNextSchool($school);
        if(!$nextSchool){
            $nextSchool = $this->schoolRepository->findFirstSchool('position', true);
        }
        return $nextSchool;
    }

    public function getPreviousSchool(School $school) {
        $previousSchool = $this->schoolRepository->findPreviousSchool($school);
        if(!$previousSchool){
            $previousSchool = $this->schoolRepository->findLastSchool('position', true);
        }
        return $previousSchool;
    }

    public function isSubscribed(School $school, User $user) {
        $subscription = $this->subscriptionRepository->findOneBy(array(
            'user' => $user,
            'school' => $school,
            'active' => true,
        ));

        if($subscription){
            return true;
        }
        return false;
    }

    public function findSchoolsSubscription(User $user) {
        $schools = array();
        $schoolSubscriptions = $this->subscriptionRepository->findBy(array(
            'user' => $user,
            'active' => true,
        ));

        foreach($schoolSubscriptions as $schoolSubscription){
            $school = $schoolSubscription->getSchool();
            if($school->getPublished()){
                array_push($schools, $school);
            }
        }

        return $schools;
    }

    public function getDocumentsByUser(User $user = null, School $school) {
        $documents = array();

        $documentTemps = $this->documentRepository->findBy(array(
            'school' => $school,
            'published' => true,
        ));

        if($user == null){
            foreach($documentTemps as $documentTemp){
                if($documentTemp->getDocumentAuthorization()->getId() == DocumentAuthorization::ANONYMOUS_ID){
                    $documents[] = $documentTemp;
                }
            }
        }else{
            if($this->isSubscribed($school, $user)){
                $documents = $documentTemps;
            }else{
                foreach($documentTemps as $documentTemp){
                    if($documentTemp->getDocumentAuthorization()->getId() == DocumentAuthorization::ANONYMOUS_ID || $documentTemp->getDocumentAuthorization()->getId() == DocumentAuthorization::CONNECTED_ID ){
                        $documents[] = $documentTemp;
                    }
                }
            }
        }

        return $documents;
    }

    public function getDocumentsConnected(School $school) {
        $authorizationConnected = $this->documentAuthorizationRepository->find(DocumentAuthorization::CONNECTED_ID);
        $documents = $this->documentRepository->findBy(array(
            'school' => $school,
            'published' => true,
            'documentAuthorization' => $authorizationConnected,
        ));

        return $documents;
    }

    public function getDocumentsSubscribed(School $school) {
        $authorizationSubscribed = $this->documentAuthorizationRepository->find(DocumentAuthorization::SUBSCRIBED_ID);
        $documents = $this->documentRepository->findBy(array(
            'school' => $school,
            'published' => true,
            'documentAuthorization' => $authorizationSubscribed,
        ));

        return $documents;
    }

    public function getValidEvaluationsByUser(User $user) {
        $evaluations = array();

        $evaluationTemps = $this->evaluationRepository->getValidEvaluationsByUser($user);
/*
        $evaluations = array();
        foreach($evaluationTemps as $evaluation){
            array_push($evaluations, $evaluation);
        }
*/
        return $evaluationTemps;
    }

    public function getPassMark($school, $maxMark = 5) {
        $passMark = 0;

        $allEvaluations = $this->evaluationRepository->findBy(array(
            'school' => $school,
            'current' => true,
        ));
        $countEvaluations = count($allEvaluations);
        foreach($allEvaluations as $evaluation){
            $passMark = $passMark + $evaluation->getMark() / $countEvaluations / 20;
        }
        if($passMark - floor($passMark) != 0){
            $passMark = number_format((float)$passMark, 2, ',', '');
        }

        return $passMark;
    }
}