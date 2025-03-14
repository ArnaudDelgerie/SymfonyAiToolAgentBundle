# SymfonyAiToolAgentBundle Introduction

## Disclaimer
- This bundle is in **alpha version**. If you use it in production, please check the `CHANGELOG.md` file before each version upgrade.
- This bundle relies on third-party APIs (**Mistral, OpenAI, and Anthropic**). It is recommended to review their **Terms of Service** and **Data Protection Policies** before usage.
- If you are not familiar with the concept of `tool function`, it is advised to get acquainted with it before using this bundle.

## Overview
This bundle enables the creation of AI agents based on the use of `tool function`. Two types of agents are available:
- **ToolAgent**: Automates text and image content processing (e.g. semantic analysis, moderation, etc.).
- **ConsoleToolAgent**: Interactive agents that allow users to perform tasks via the Symfony console using custom commands.


## Requirements
Since this bundle integrates third-party APIs, you need to create an account with at least one of the following providers:
- **[OpenAI](https://platform.openai.com/docs/overview)**
- **[Mistral](https://docs.mistral.ai/api/)** *(free plan available)*
- **[Anthropic](https://www.anthropic.com/api)**


## Quickstart

### Purpose :
Our goal is to assign a distinct and relevant topic to the user's content.

### 1st solution :
**Step 1 :** create empty `TopicClassificationToolFunctionManager`
```php
<?php

namespace App\ToolFunctionManager;

use ArnaudDelgerie\AiToolAgent\DTO\ToolFunction;
use ArnaudDelgerie\AiToolAgent\Util\ToolResponse;
use ArnaudDelgerie\AiToolAgent\Interface\ToolFunctionManagerInterface;

class TopicClassificationToolFunctionManager implements ToolFunctionManagerInterface
{   
    public static function getName(): string
    {
        return 'topic_classification';
    }

    public function getToolFunction(array $context): ToolFunction
    {
        return (new ToolFunction());
    }

    public function execute(array $args, array $context, array $responseContent): ToolResponse
    {
        return new ToolResponse($responseContent, "");
    }
}
```

**Step 2 :** write the `/prompts/topic_classification.txt` system prompt
```
You are a topic classification agent,for each content to analyze you only 
have to make one call to the "topic_classification" function to categorize it
```

**Step 3 :** create `TopicClassifier`
```php
<?php

namespace App\Service;

use App\Entity\Post;
use App\Repository\TopicRepository;
use ArnaudDelgerie\AiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\AiToolAgent\Util\ToolAgentProvider;
use ArnaudDelgerie\AiToolAgent\Util\Config\AgentConfig;
use ArnaudDelgerie\AiToolAgent\Util\Config\ClientConfig;
use App\ToolFunctionManager\TopicClassificationToolFunctionManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TopicClassifier
{
    public function __construct(
        private TopicRepository       $topicRepo,
        private ParameterBagInterface $params,
        private ToolAgentProvider     $toolAgentProvider
    ) {}

    public function setPostTopic(Post $post): void
    {
        // Replace with Symfony secrets in production
        $apiKey = 'Your client API key (OpenAI, Mistral, or Anthropic)';
        
        // Configure AI client (model: Mistral-small-latest, temp: 0.1)
        $clientConfig = new ClientConfig(ClientEnum::Mistral, $apiKey, 'mistral-small-latest', 0.1);
        
        // Retrieve available topic slugs from the repository
        $topicSlugs = $this->topicRepo->getTopicSlugs();
        
        // Define context for the topic classification tool
        $context = ['availableTopics' => $topicSlugs, 'postId' => $post->getId()];
        
        // Specify the function(s) the tool agent can use
        $functionNames = [TopicClassificationToolFunctionManager::getName()];
        
        // Load system prompt for topic classification
        $systemPrompt = file_get_contents($this->params->get('kernel.project_dir') . '/prompts/topic_classification.txt');
        
        // Configure the tool agent
        $agentConfig = new AgentConfig($systemPrompt, $functionNames, $context);
        
        // Initialize the tool agent with client and agent configuration
        $toolAgent = $this->toolAgentProvider->createToolAgent($clientConfig, $agentConfig);
        
        // Get post content
        $content = $post->getContent();
        /*
        $content = "Function calling in LLMs allows AI models to interact with external functions by generating structured 
        outputs that trigger specific functions. This enhances their capabilities beyond text generation, enabling them to 
        retrieve real-time data, perform calculations, or automate tasks. By interpreting user queries and calling relevant
        functions, LLMs become more dynamic, efficient, and useful in practical applications."
        */
        
        // Run classification by adding the content as a user message
        $response = $toolAgent->addUserMessage($content)->run();
        
        // Retrieve API usage report
        // Example: ["nbRequest" => 1, "promptTokens" => 280, "completionTokens" => 30, "totalToken" => 310]
    }
}
```

**Step 4 :** Update ToolFunctionManager class
```php
<?php

namespace App\ToolFunctionManager;

use App\Repository\PostRepository;
use App\Repository\TopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use ArnaudDelgerie\AiToolAgent\DTO\ToolFunction;
use ArnaudDelgerie\AiToolAgent\Util\ToolResponse;
use ArnaudDelgerie\AiToolAgent\DTO\ToolFunctionProperty;
use ArnaudDelgerie\AiToolAgent\Enum\ToolFunctionPropertyTypeEnum;
use ArnaudDelgerie\AiToolAgent\Interface\ToolFunctionManagerInterface;

class TopicClassificationToolFunctionManager implements ToolFunctionManagerInterface
{
    public function __construct(
        private TopicRepository        $topicRepo,
        private PostRepository         $postRepo,
        private EntityManagerInterface $em
    ) {}

    public static function getName(): string
    {
        return 'topic_classification';
    }

    public function getToolFunction(array $context): ToolFunction
    {
        // Define the tool function
        return (new ToolFunction())
            // Ensure the function name matches self::getName()
            ->setName(self::getName())
            // Provide a description to help the tool agent determine when to use it
            ->setDescription('Categorizes content into predefined topics')
            // Define required properties for the function
            ->addProperty(
                'topic',
                (new ToolFunctionProperty())
                    ->setType(ToolFunctionPropertyTypeEnum::String)
                    ->setDescription('The most relevant topic for the content')
                    // Use available topics from the provided context
                    ->setEnum($context['availableTopics'])
            )
            ->addProperty(
                'prediction_confidence',
                (new ToolFunctionProperty())
                    ->setType(ToolFunctionPropertyTypeEnum::Number)
                    ->setDescription('Confidence score of the classification (0-10)')
            );
    }

    public function execute(array $args, array $context, array $responseContent): ToolResponse
    {
        // $args contains the function property values set by the tool agent
        // Example: ['topic' => 'technology', 'prediction_confidence' => 10]
        
        // Retrieve the Post using the provided postId
        $post = $this->postRepo->find($context['postId']);

        // Retrieve the Topic using the provided slug
        $topic = $this->topicRepo->findOneBy(['slug' => $args['topic']]);

        // Assign topic and confidence score to the post
        $post->setTopic($topic)->setTopicConfidence((int)$args['prediction_confidence']);
        $this->em->flush();

        // The second parameter (validation message) is left empty since only one task is performed
        // The third parameter (stopRun) is set to true to terminate execution
        return new ToolResponse($responseContent, "", true);
    }
}
```

### 2nd solution :
In the first solution, `TopicClassificationToolFunctionManager` directly handles the `Post` update logic. While this approach works, it may lack flexibility, especially if you need to extend classification to other entities, such as comments. To improve adaptability, we'll refactor the logic to pass the result via `$responseContent` instead.

**Step 1 :** update `TopicClassificationToolFunctionManager`
```php
<?php

namespace App\ToolFunctionManager;

use ArnaudDelgerie\AiToolAgent\DTO\ToolFunction;
use ArnaudDelgerie\AiToolAgent\Util\ToolResponse;
use ArnaudDelgerie\AiToolAgent\DTO\ToolFunctionProperty;
use ArnaudDelgerie\AiToolAgent\Enum\ToolFunctionPropertyTypeEnum;
use ArnaudDelgerie\AiToolAgent\Interface\ToolFunctionManagerInterface;

class TopicClassificationToolFunctionManager implements ToolFunctionManagerInterface
{
    // we can remove the _construct method because we no longer need the repositories and the entity manager
    
    public static function getName(): string
    {
        return 'topic_classification';
    }

    public function getToolFunction(array $context): ToolFunction
    {
        // no change here, we keep the code from the 1st solution
    }

    public function execute(array $args, array $context, array $responseContent): ToolResponse
    {
        // Add result to $responseContent
        $responseContent[self::getName()] = $args;
        
        return new ToolResponse($responseContent, "", true);
    }
}
```

**Step 2 :** update `TopicClassifier`
```php
<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\Comment;
use App\Repository\TopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use ArnaudDelgerie\AiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\AiToolAgent\Util\ToolAgentProvider;
use ArnaudDelgerie\AiToolAgent\Util\Config\AgentConfig;
use ArnaudDelgerie\AiToolAgent\Util\Config\ClientConfig;
use App\ToolFunctionManager\TopicClassificationToolFunctionManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TopicClassifier
{
    public function __construct(
        private EntityManagerInterface $em
        private TopicRepository        $topicRepo,
        private ParameterBagInterface  $params,
        private ToolAgentProvider      $toolAgentProvider
    ) {}
    
    private function getContentTopic(string $content): array
    {
        $apiKey = 'Your client api key (openai, mistral or anthropic)';
        $clientConfig = new ClientConfig(ClientEnum::Mistral, $apiKey, 'mistral-small-latest', 0.1); 
        
        // Remove postId from context
        $context = ['availableTopics' => $this->topicRepo->getTopicSlugs()];
        $functionNames = [TopicClassificationToolFunctionManager::getName()];
        $systemPrompt = file_get_contents($this->params->get('kernel.project_dir') . '/prompts/topic_classification.txt');
        $agentConfig = new AgentConfig($systemPrompt, $functionNames, $context);
        
        $toolAgent = $this->toolAgentProvider->createToolAgent($clientConfig, $agentConfig);
        $response = $toolAgent->addUserMessage($content)->run();
        
        /*
        $response->content
        [
          "availableTopics" => [the list of initial available topics],
          "topic_classification" => ["topic" => "technology", "prediction_confidence" => 10],
		]
		*/

        return $response->content[TopicClassificationToolFunctionManager::getName()];
    }
    
     // now we can use tool agent to set Post topic
    public function setPostTopic(Post $post): void
    {
        $result = $this->getContentTopic($post->getContent());
        $topic = $this->topicRepo->findOneBy(['slug' => $result['topic']]);

        $post->setTopic($topic)->setTopicConfidence((int)$result['prediction_confidence']);
        $this->em->flush();
    }
    
    // and also to set Comment topic
    public function setCommentTopic(Comment $comment): void
    {
        $result = $this->getContentTopic($comment->getContent());
        $topic = $this->topicRepo->findOneBy(['slug' => $result['topic']]);

        $comment->setTopic($topic)->setTopicConfidence((int)$result['prediction_confidence']);
        $this->em->flush();
    }
}
```